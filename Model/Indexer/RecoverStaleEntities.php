<?php

namespace Wizzy\Search\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Model\EntitiesSyncFactory;
use Wizzy\Search\Model\QueueFactory;
use Wizzy\Search\Services\Model\EntitiesSync;

class RecoverStaleEntities implements ActionInterface
{
    const ENTITY_IN_SYNC_STATUS = 0;
    const JOB_IN_PROGRESS_STATUS = 1;
    const JOB_TO_EXECUTE_STATUS = 0;
    const ENTITY_TYPE_PRODUCT = 'product';

    private $indexer;
    private $queueFactory;
    private $storeManager;
    private $entitiesSyncFactory;
    private $entitiesSync;

    public function __construct(
        IndexerManager $indexerManager,
        QueueFactory $queueFactory,
        StoreManager $storeManager,
        EntitiesSyncFactory $entitiesSyncFactory,
        EntitiesSync $entitiesSync
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->queueFactory = $queueFactory;
        $this->storeManager = $storeManager;
        $this->entitiesSyncFactory = $entitiesSyncFactory;
        $this->entitiesSync = $entitiesSync;
    }

    public function execute()
    {
        return $this;
    }

    public function executeRow($id)
    {
        return $this;
    }

    public function executeList($ids)
    {
        return $this;
    }

    public function executeFull()
    {
        $storeWiseInSyncEntityIds = [];
        $inSyncEntities = $this->getEntitiesInSync(
            self::ENTITY_TYPE_PRODUCT,
            self::ENTITY_IN_SYNC_STATUS
        );

        foreach ($inSyncEntities as $inSyncEntity) {
            $storeWiseInSyncEntityIds[$inSyncEntity['store_id']][] =
                $inSyncEntity['entity_id'];
        }

        $inQueueAndInProgressIds = [];
        $inQueueAndInProgressJobs = $this->getInQueueAndInProgressJobs(
            [self::JOB_TO_EXECUTE_STATUS, self::JOB_IN_PROGRESS_STATUS],
            null
        );

        foreach ($inQueueAndInProgressJobs as $job) {
            $jobData = json_decode($job['data'], true);

            if (isset($jobData['products'])) {
                $storeId = $job['store_id'];

                if (!isset($inQueueAndInProgressIds[$storeId])) {
                    $inQueueAndInProgressIds[$storeId] = [];
                }

                foreach ($jobData['products'] as $productId) {
                    $inQueueAndInProgressIds[$storeId][] = $productId;
                }
            }
        }

        $productIdsToBeHandled = [];

        foreach ($storeWiseInSyncEntityIds as $storeId => $productIds) {
            if (isset($inQueueAndInProgressIds[$storeId])) {
                $difference = array_diff(
                    $productIds,
                    $inQueueAndInProgressIds[$storeId]
                );

                if (!empty($difference)) {
                    $productIdsToBeHandled[$storeId] = $difference;
                }
            } else {
                $productIdsToBeHandled[$storeId] = $productIds;
            }
        }

        $uniqueProductIdsToBeHandled = [];

        foreach ($productIdsToBeHandled as $storeId => $ids) {
            foreach ($ids as $id) {
                $uniqueProductIdsToBeHandled[$id] = $id;
            }
        }

        $uniqueProductIdsToBeHandled = array_values($uniqueProductIdsToBeHandled);

        foreach ($productIdsToBeHandled as $storeId => $ids) {
            $this->entitiesSync->markEntitiesAsSynced(
                $ids,
                $storeId,
                EntitiesSync::ENTITY_TYPE_PRODUCT
            );
        }

        if (!empty($uniqueProductIdsToBeHandled) && !$this->indexer->isScheduled()) {
            $this->indexer->reindexList($uniqueProductIdsToBeHandled);
        }
    }

    public function getInQueueAndInProgressJobs($status, $jobClass = null)
    {
        $jobs = $this->queueFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', ['in' => $status]);

        if ($jobClass) {
            $jobs->addFieldToFilter('class', $jobClass);
        }

        $jobs->setOrder('id', 'ASC');

        $jobsData = [];

        foreach ($jobs as $job) {
            $jobsData[] = $job->getData();
        }

        return $jobsData;
    }

    public function getEntitiesInSync($entityType, $entityStatus)
    {
        $entitiesSync = $this->entitiesSyncFactory->create();

        return $entitiesSync->getCollection()
            ->addFieldToFilter('entity_type', ['eq' => $entityType])
            ->addFieldToFilter('status', ['eq' => $entityStatus])
            ->getItems();
    }
}
