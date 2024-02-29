<?php

namespace Wizzy\Search\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Debug extends Action
{
    protected $backendSession;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        BackendSession $backendSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->backendSession = $backendSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $breadCrumbTitle = __('Wizzy Search | Debug Sync');
        $syncDebugResult = $this->backendSession->getApiResponse();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($breadCrumbTitle);
        $responseBlock = $resultPage->getLayout()->createBlock(
            \Magento\Framework\View\Element\Text::class
        );
        $syncDebugResult = json_encode($syncDebugResult, JSON_PRETTY_PRINT);
        $responseBlock->setText('<pre>' . $syncDebugResult . '</pre>');
        $resultPage->addContent($responseBlock, 'content');
        return $resultPage;
    }
}
