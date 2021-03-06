<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Queue\Processors\IndexPagesProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Magento;

class Pages implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{

    private $queueManager;
    private $output;

    public function __construct(QueueManager $queueManager, IndexerOutput $output)
    {
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
