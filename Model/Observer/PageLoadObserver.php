<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Request\CategoryManager;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Magento\Framework\Event\Observer;

class PageLoadObserver implements ObserverInterface
{

    private $storeGeneralConfig;
    private $categoryRequestManager;
    private $request;

    public function __construct(
        StoreGeneralConfig $storeGeneralConfig,
        CategoryManager $categoryRequestManager,
        RequestInterface $request
    ) {
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->categoryRequestManager = $categoryRequestManager;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $layout = $observer->getData('layout');

        $isAutocompleteEnabled = $this->storeGeneralConfig->isAutocompleteEnabled();
        $isInstantSearchEnabled = $this->storeGeneralConfig->isInstantSearchEnabled();
        $hasToReplaceCategoryPage = $this->storeGeneralConfig->hasToReplaceCategoryPage();

        if ($this->request->getModuleName() === "checkout"
           && $this->request->getFullActionName() === "checkout_index_index"
        ) {
            return;
        }

        if ($isAutocompleteEnabled || $isInstantSearchEnabled) {
            $layout->getUpdate()->addHandle('wizzy_search_common');
            $layout->getUpdate()->addHandle('wizzy_search_formmini');

            if ($isAutocompleteEnabled) {
                $layout->getUpdate()->addHandle('wizzy_search_autocomplete');
            }
            if ($isInstantSearchEnabled) {
                $layout->getUpdate()->addHandle('wizzy_search_instant');

                if ($hasToReplaceCategoryPage && $this->categoryRequestManager->isCategoryReplaceable()) {
                    $layout->getUpdate()->addHandle('wizzy_search_category');
                }
            }

        }
    }
}
