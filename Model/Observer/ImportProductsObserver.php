<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Queue\Processors\AddImportedProductsInQueueProcessor;

class ImportProductsObserver implements ObserverInterface
{
    private $indexer;
    private $productsManager;
    private $configurable;
    private $directoryList;
    private $queueManager;
    private $storeManager;
    private $filesystem;
    public function __construct(
        IndexerManager $indexerManager,
        ProductsManager $productsManager,
        Configurable $configurable,
        DirectoryList $directoryList,
        QueueManager $queueManager,
        StoreManager $storeManager,
        Filesystem $filesystem
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->productsManager = $productsManager;
        $this->configurable = $configurable;
        $this->directoryList = $directoryList;
        $this->queueManager = $queueManager;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }
    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getData('bunch');
        $SKUs = array_column($bunch, 'sku');

        $storeIds = $this->storeManager->getToSyncStoreIds();
        if ($storeIds) {
            foreach ($storeIds as $storeId) {
                $data = $this->createSkuFile($SKUs, $storeId);
                if ($data) {
                    $this->queueManager->enqueue(
                        AddImportedProductsInQueueProcessor::class,
                        $storeId,
                        $data
                    );
                }
            }
        }
    }

    public function createSkuFile(array $skus, $storeId)
    {
        $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $directoryPath = 'wizzy_import/';
        if (!$varDirectory->isExist($directoryPath)) {
            try {
                $varDirectory->create($directoryPath);
            } catch (\Exception $e) {
                return false;
            }
        }

        $fileName = 'import_skus_' . date('Ymd_His') . '_store' . $storeId . '.json';
        $fullFilePath = $directoryPath . $fileName;

        $varDirectory->writeFile(
            $fullFilePath,
            json_encode($skus, JSON_THROW_ON_ERROR)
        );

        if ($varDirectory->isExist($fullFilePath)) {
            return [
                'fileName' => $fileName,
                'filePath' => $fullFilePath,
            ];
        }

        return false;
    }
}
