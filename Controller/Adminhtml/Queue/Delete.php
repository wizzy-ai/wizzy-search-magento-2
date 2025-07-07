<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\Processors\IndexProductsProcessor;

class Delete extends Action
{
    private $flashMessagesManager;
    private $queueManager;
    private $entitiesSync;

   /**
    * @param Context $context
    * @param FlashMessagesManager $flashMessagesManager
    * @param QueueManager $queueManager
    */
    public function __construct(
        Context $context,
        FlashMessagesManager $flashMessagesManager,
        QueueManager $queueManager,
        EntitiesSync $entitiesSync
    ) {
        parent::__construct($context);
        $this->flashMessagesManager = $flashMessagesManager;
        $this->queueManager = $queueManager;
        $this->entitiesSync = $entitiesSync;
    }

    public function execute()
    {
        $queueId = (int) $this->getRequest()->getParam('id');
        $queueRow = $this->queueManager->get($queueId);
        
        if (!$queueId || !$queueRow) {
            $this->flashMessagesManager->error(__('This queue item does not exist.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('*/*/status');
        }

        if ($queueId) {
            $deleted = $this->queueManager->deleteJob($queueId);
        }

        if ($queueRow && $queueRow['class'] == IndexProductsProcessor::class) {
            $decodedData = json_decode($queueRow['data'], true);
            $productIds = $decodedData['products'] ?? [];
            $storeID = $queueRow['store_id'] ?? null;
            
            if (!empty($productIds)) {
                $this->entitiesSync->markEntitiesAsSynced(
                    $productIds,
                    $storeID,
                    EntitiesSync::ENTITY_TYPE_PRODUCT
                );
            }
        }

        if ($deleted) {
            $this->flashMessagesManager->success(__('Queue item has been deleted successfully.'));
        } else {
            $this->flashMessagesManager->error(__('Queue item could not be deleted.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('*/*/status');
    }
}
