<?php

namespace Wizzy\Search\Controller\Adminhtml\Config;

use \Magento\Framework\App\Request\Http;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Cache\Type\Config;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\App\Cache\TypeListInterface;
use \Magento\Framework\App\Config\Storage\WriterInterface;
use \Wizzy\Search\Services\Store\StoreCredentialsConfig;
use \Wizzy\Search\Services\Store\StoreGeneralConfig;

class ResetConfig extends Action
{
    protected $request;
    protected $context;
    protected $configWriter;
    protected $cacheTypeList;
    protected $messageManager;
    protected $storeCredentialsConfig;
    protected $storeGeneralConfig;

    public function __construct(
        Http $request,
        Context $context,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        ManagerInterface $messageManager,
        StoreCredentialsConfig $storeCredentialsConfig,
        StoreGeneralConfig $storeGeneralConfig
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->messageManager = $messageManager;
        $this->storeCredentialsConfig = $storeCredentialsConfig;
        $this->storeGeneralConfig = $storeGeneralConfig;
        return parent::__construct($context);
    }

    public function resetConfig(string $path)
    {
        $scopeId = $this->request->getParam('store');
        $this->configWriter->save($path, $value = "", $scope = "stores", (int)$scopeId);
    }

    public function resetGeneralConfig(string $path)
    {
        $scopeId = $this->request->getParam('store');
        $this->configWriter->save($path, $value = 0, $scope = "stores", (int)$scopeId);
    }

    public function execute()
    {
        $this->resetConfig(StoreCredentialsConfig::WIZZY_STORE_ID);
        $this->resetConfig(StoreCredentialsConfig::WIZZY_STORE_SECRET);
        $this->resetConfig(StoreCredentialsConfig::WIZZY_STORE_API_KEY);
        $this->resetGeneralConfig(StoreGeneralConfig::IS_SEARCH_ENABLED);
        $this->resetGeneralConfig(StoreGeneralConfig::IS_AUTOCOMPLETE_ENABLED);
        $this->resetGeneralConfig(StoreGeneralConfig::IS_ANALYTICS_ENABLED);
        $this->cacheTypeList->cleanType(Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType('full_page');
        $message = __('Credentials have been removed successfully.');
        $this->messageManager->addSuccessMessage($message);
    }
}
