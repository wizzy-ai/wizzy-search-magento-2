<?php

namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Model\DeletedProducts;
use Wizzy\Search\Model\Indexer\Products;
use Magento;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Model\EntitiesSync;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\MviewActionInterface;

class RecoverDeletedProducts implements ActionInterface, MviewActionInterface
{

    private $output;
    private $deletedProducts;
    private $productsIndexer;
    private $storeManager;
    private $entitiesSync;

    public function __construct(
        IndexerOutput $output,
        Products $productsIndexer,
        DeletedProducts $deletedProducts,
        StoreManager $storeManager,
        EntitiesSync $entitiesSync
    ) {
        $this->output = $output;
        $this->productsIndexer =$productsIndexer;
        $this->deletedProducts = $deletedProducts;
        $this->storeManager = $storeManager;
        $this->entitiesSync = $entitiesSync;
    }

    public function executeFull()
    {
        $deletedProductIds = $this->deletedProducts->getDeletedProducts();
        $this->output->writeDiv();
        $this->output->writeln('Started Recovering Deleted Products');
        if (count($deletedProductIds) == 0) {
            $this->output->writeln('No deleted products found at this moment.');
        } else {
            $storeIds = $this->storeManager->getToSyncStoreIds();
            foreach ($storeIds as $storeId) {
                $this->entitiesSync->markEntitiesAsSynced(
                    $deletedProductIds,
                    $storeId,
                    EntitiesSync::ENTITY_TYPE_PRODUCT
                );
            }
            $this->productsIndexer->executeList($deletedProductIds);
            $this->output->writeln('Added '.count($deletedProductIds). ' deleted products for sync sucessfully.');
        }
        return $this;
    }

    public function executeList(array $ids)
    {
        return $this;
    }

    public function executeRow($id)
    {
        return $this;
    }

    public function execute($ids)
    {
        return $this;
    }
}
