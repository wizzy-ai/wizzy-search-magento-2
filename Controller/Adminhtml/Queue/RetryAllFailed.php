<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Queue\QueueManager;

class RetryAllFailed extends Action
{
    private $queueManager;
    private $flashMessagesManager;

    public function __construct(
        Context $context,
        QueueManager $queueManager,
        FlashMessagesManager $flashMessagesManager
    ) {
        parent::__construct($context);
        $this->queueManager = $queueManager;
        $this->flashMessagesManager = $flashMessagesManager;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/status');
        $count = $this->queueManager->retryAllFailedJobs();
        if ($count > 0) {
            $this->flashMessagesManager->success(
                __('%1 failed batch(es) have been put back in queue for retry.', $count)
            );
        } else {
            $this->flashMessagesManager->warning(__('No failed batches to retry.'));
        }
        return $resultRedirect;
    }
}
