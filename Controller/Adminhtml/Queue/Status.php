<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class Status extends Action
{
    public function execute()
    {
        $breadCrumbTitle = __('Wizzy Search | Queue Processors Status');
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Wizzy_Search::main');
        $resultPage->getConfig()->getTitle()->prepend($breadCrumbTitle);
        return $resultPage;
    }
}
