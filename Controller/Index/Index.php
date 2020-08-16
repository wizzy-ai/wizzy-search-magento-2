<?php

namespace Wizzy\Search\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class Index extends Action
{

    protected $_pageFactory;

   /**
    * @param Context $context
    * @param PageFactory $pageFactory
    */
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->_pageFactory->create();
        $page->addPageLayoutHandles([], 'wizzy_search_page');
        $page->getConfig()->getTitle()->set(__('Search'));

        return $page;
    }
}
