<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\Session;
use Magento\Config\Model\Config;
use Wizzy\Search\Services\Store\StoreSyncDebugConfig;
use Wizzy\Search\Services\Queue\Processors\IndexProductsProcessor;
use Wizzy\Search\Services\Catalogue\Mappers\ProductsMapper;

class DebugSyncFormSubmitted implements ObserverInterface
{
    private $session;
    private $ProductsMapper;
    private $storeSyncDebugConfig;
    private $IndexProductsProcessor;
    private $managerInterface;
    private $config;

    public function __construct(
        Session $session,
        ProductsMapper $ProductsMapper,
        StoreSyncDebugConfig $storeSyncDebugConfig,
        IndexProductsProcessor $IndexProductsProcessor,
        ManagerInterface $managerInterface,
        Config $config
    ) {
        $this->session = $session;
        $this->ProductsMapper = $ProductsMapper;
        $this->storeSyncDebugConfig = $storeSyncDebugConfig;
        $this->IndexProductsProcessor = $IndexProductsProcessor;
        $this->managerInterface = $managerInterface;
        $this->config = $config;
    }

    public function execute(EventObserver $observer)
    {

        $productIdsToBeDebugged = $this->storeSyncDebugConfig
            ->getProductIdsTobeDebugged();
        $storeId = $this->storeSyncDebugConfig->getStoreId();

        if ($this->isValidJson($productIdsToBeDebugged)) {
            $data = ['products' => json_decode($productIdsToBeDebugged)];
            $debugSyncApiResponse = $this->IndexProductsProcessor
                ->execute($data, $storeId);
            if ($debugSyncApiResponse) {
                $debugSyncResult = $this->ProductsMapper->getLastProcessedProducts();
                $this->session->setApiResponse($debugSyncResult);
                $this->config->setMessage(null);
                if (isset($debugSyncResult['toAdd']) || isset($debugSyncResult['toDelete'])) {
                    $this->managerInterface->addSuccessMessage(
                        "Please click on View Debug Result button to 
                         check the response on new page"
                    );
                } else {
                    $this->managerInterface->addWarningMessage(
                        "Please check if the product ID exist in your 
                         catalog OR Wizzy sync is enabled for the selected store"
                    );
                }
            }
        } else {
            $this->managerInterface->addErrorMessage(
                "Please enter valid product IDs JSON array and try again"
            );
        }
    }

    private function isValidJson($jsonString)
    {
        json_decode($jsonString);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
