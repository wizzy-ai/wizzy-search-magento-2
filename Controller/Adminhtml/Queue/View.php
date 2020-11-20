<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Queue\QueueManager;

class View extends Action
{
    private $flashMessagesManager;
    private $queueManager;

   /**
    * @param Context $context
    * @param FlashMessagesManager $flashMessagesManager
    * @param QueueManager $queueManager
    */
    public function __construct(
        Context $context,
        FlashMessagesManager $flashMessagesManager,
        QueueManager $queueManager
    ) {
        parent::__construct($context);
        $this->flashMessagesManager = $flashMessagesManager;
        $this->queueManager = $queueManager;
    }

    public function execute()
    {
        $queueId = (int) $this->getRequest()->getParam('id');
        $queue = $this->queueManager->get($queueId);

        if (!$queueId || !$queue) {
            $this->flashMessagesManager->error(__('This queue item does not exists.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $breadcrumbTitle = __('View Job');
        $resultPage
         ->setActiveMenu('Wizzy_Search::main')
         ->addBreadcrumb(__('Queue Processors'), __('Queue Processors'))
         ->addBreadcrumb($breadcrumbTitle, $breadcrumbTitle);

        $resultPage->getConfig()->getTitle()->prepend(__('Queue Processors'));
        $resultPage->getConfig()->getTitle()->prepend(__('View Queue Processor #%1', $queueId));

        return $resultPage;
    }
}
