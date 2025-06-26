<?php
namespace Wizzy\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Add extends Action
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Wizzy_Search::synonyms');
        $resultPage->getConfig()->getTitle()->prepend(__('Add new Synonym'));
        return $resultPage;
    }
}
