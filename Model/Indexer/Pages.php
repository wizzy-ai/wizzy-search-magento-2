<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Queue\Processors\IndexPagesProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Magento;
use Wizzy\Search\Services\Store\StoreManager;

class Pages implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{
    private $storeAutocompleteConfig;
    private $queueManager;
    private $output;
    private $storeManager;

    public function __construct(
        QueueManager $queueManager,
        IndexerOutput $output,
        StoreAutocompleteConfig $storeAutocompleteConfig,
        StoreManager $storeManager
    ) {
        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->queueManager = $queueManager;
        $this->output = $output;
        $this->storeManager = $storeManager;
    }

   /*
    * No need to execute anything.
    */
    public function execute($ids)
    {
        return $this;
    }

   /*
    * Execute Pages Indexer
    */
    public function executeFull()
    {
        $this->addPagesForSync([]);
        return $this;
    }

   /*
    * Execute Pages Indexer
    */
    public function executeList(array $slugs)
    {
        $this->addPagesForSync($slugs);
        return $this;
    }

   /*
    * Execute Pages Indexer
    */
    public function executeRow($slug)
    {
        $this->addPagesForSync([$slug]);
        return $this;
    }

    private function addPagesForSync($slugs)
    {
        $storeIds = $this->storeManager->getToSyncStoreIds();
        
        foreach ($storeIds as $storeId) {
            $this->storeAutocompleteConfig->setStore($storeId);
            if ($this->storeAutocompleteConfig->hasToSyncPages() == false) {
                $this->output->writeDiv();

                $this->output->writeln(__(
                    "Adding Pages in Sync Skipped for Store Id #" . $storeId .
                    " (Based on Sync Pages configuration)"
                ));
                continue;
            }
            $this->queueManager->enqueue(IndexPagesProcessor::class, $storeId, [
            'slugs' => $slugs,
            ]);

            $this->output->writeDiv();
            $this->output->writeln(__('Added all Pages for Sync for the store Id #' . $storeId));
        }
    }
}
