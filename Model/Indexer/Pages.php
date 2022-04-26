<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Queue\Processors\IndexPagesProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Magento;

class Pages implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{
    private $storeAutocompleteConfig;
    private $queueManager;
    private $output;

    public function __construct(
        QueueManager $queueManager,
        IndexerOutput $output,
        StoreAutocompleteConfig $storeAutocompleteConfig
    ) {
        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->queueManager = $queueManager;
        $this->output = $output;
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

        if ($this->storeAutocompleteConfig->hasToSyncPages() == false) {
            return true;
        }
        if (!count($slugs)) {
            $this->queueManager->clear(0, IndexPagesProcessor::class);
        }
        $this->queueManager->enqueue(IndexPagesProcessor::class, 0, [
         'slugs' => $slugs,
        ]);
        $this->output->writeDiv();
        $this->output->writeln(__('Added ' . count($slugs) . ' Pages for Sync.'));
    }
}
