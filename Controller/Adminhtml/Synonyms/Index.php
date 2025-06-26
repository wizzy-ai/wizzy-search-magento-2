<?php
namespace Wizzy\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public function execute()
    {
        $breadCrumbTitle = __('Wizzy Search | Synonyms');
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Wizzy_Search::main');
        $resultPage->getConfig()->getTitle()->prepend($breadCrumbTitle);

        return $resultPage;
    }
}
