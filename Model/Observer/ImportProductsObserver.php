<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Import\ImportedSkusCollector;

class ImportProductsObserver implements ObserverInterface
{
    /**
     * @var ImportedSkusCollector
     */
    private $importedSkusCollector;

    public function __construct(ImportedSkusCollector $importedSkusCollector)
    {
        $this->importedSkusCollector = $importedSkusCollector;
    }

    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getData('bunch');
        if (!$bunch) {
            return;
        }

        $skus = array_column($bunch, 'sku');
        $this->importedSkusCollector->addSkus($skus);
    }
}
