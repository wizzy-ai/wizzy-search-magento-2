<?php

namespace Wizzy\Search\Controller\Adminhtml\Sync;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class Skippedentities extends Action
{
    public function execute()
    {
        $breadCrumbTitle = __('Wizzy Search | Skipped Entities');
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Wizzy_Search::main');
        $resultPage->getConfig()->getTitle()->prepend($breadCrumbTitle);
        return $resultPage;
    }
}
