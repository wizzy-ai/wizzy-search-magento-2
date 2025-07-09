<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Wizzy\Search\Model\Observer\ProductsObserver;
use Magento\Framework\Filesystem;

class AddImportedProductsInQueueProcessor extends QueueProcessorBase
{
    private $productsIndexer;
    private $storeGeneralConfig;
    private $productsManager;
    private $output;
    private $productsObserver;
    private $filesystem;

    public function __construct(
        IndexerManager $indexerManager,
        ProductsManager $productsManager,
        StoreGeneralConfig $storeGeneralConfig,
        IndexerOutput $output,
        ProductsObserver $productsObserver,
        Filesystem $filesystem
    ) {
        $this->productsIndexer = $indexerManager->getProductsIndexer();
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->productsManager = $productsManager;
        $this->output = $output;
        $this->productsObserver = $productsObserver;
        $this->filesystem = $filesystem;
    }

    public function execute(array $data, $storeId)
    {
        $this->storeGeneralConfig->setStore($storeId);

        if (!$this->storeGeneralConfig->isSyncEnabled()) {
            $this->output->writeln(
                'Add Imported Products In Queue Processor Skipped: Sync is disabled for the store ID: ' . $storeId
            );
            return false;
        }

        if (empty($data['fileName'])) {
            $this->output->writeln('Skipped: fileName is not in the queue item.');
            return false;
        }

        $this->output->writeln('Started processing file: ' . $data['fileName']);

        $SKUs = [];
        try {
            $SKUs = $this->getSkusFromFile($data['fileName']);
        } catch (\Exception $e) {
            $this->output->writeln('Error reading SKUs from file: ' . $e->getMessage());
            return false;
        }

        if (empty($SKUs)) {
            $this->output->writeln('Skipped: No SKUs found in file: ' . $data['fileName']);
            return false;
        }

        $products = $this->productsManager->getProductsBySKUs($SKUs);
        $productIds = $this->productsManager->getProductIds($products);
        $productIdsToIndex = $this->productsObserver->getProductIdsToIndex($productIds);

        if (!$this->productsIndexer->isScheduled() && !empty($productIdsToIndex)) {
            $this->productsIndexer->reindexList($productIdsToIndex);
        }

        return true;
    }
    public function getSkusFromFile($fileName)
    {
        $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $filePath = 'wizzy_import/' . $fileName;

        if ($varDirectory->isExist($filePath)) {
            $fileContents = $varDirectory->readFile($filePath);
            $skus = json_decode($fileContents, true, 512, JSON_THROW_ON_ERROR);
            $varDirectory->delete($filePath);
            return $skus;
        }
        return [];
    }
}
