<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem;
use Wizzy\Search\Services\Import\ImportedSkusCollector;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Queue\Processors\AddImportedProductsInQueueProcessor;

class ImportProductsFinishObserver implements ObserverInterface
{
    private $queueManager;
    private $storeManager;
    private $filesystem;
    /**
     * @var ImportedSkusCollector
     */
    private $importedSkusCollector;

    public function __construct(
        QueueManager $queueManager,
        StoreManager $storeManager,
        Filesystem $filesystem,
        ImportedSkusCollector $importedSkusCollector
    ) {
        $this->queueManager = $queueManager;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->importedSkusCollector = $importedSkusCollector;
    }

    public function execute(Observer $observer)
    {
        $skus = $this->importedSkusCollector->getSkus();
        if (empty($skus)) {
            return;
        }

        $varDirectory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);

        $directoryPath = 'wizzy_import/';
        if (!$varDirectory->isExist($directoryPath)) {
            try {
                $varDirectory->create($directoryPath);
            } catch (\Exception $e) {
                return;
            }
        }

        $storeIds = $this->storeManager->getToSyncStoreIds();

        foreach ($storeIds as $storeId) {
            $fileName = sprintf(
                'import_skus_%s_store%d.json',
                date('Ymd_His'),
                $storeId
            );

            $filePath = $directoryPath . $fileName;
            try {
                $varDirectory->writeFile(
                    $filePath,
                    json_encode($skus, JSON_THROW_ON_ERROR)
                );
            } catch (\Exception $e) {
                return;
            }
            $this->queueManager->enqueue(
                AddImportedProductsInQueueProcessor::class,
                $storeId,
                [
                    'fileName' => $fileName,
                    'filePath' => $filePath
                ]
            );
        }

        // Avoid leaking state across multiple import runs in the same process.
        $this->importedSkusCollector->clear();
    }
}
