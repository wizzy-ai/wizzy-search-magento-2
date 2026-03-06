<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Queue\QueueManager;

class Retry extends Action
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
        $queueId = (int) $this->getRequest()->getParam('id');
        if (!$queueId) {
            $this->flashMessagesManager->error(__('Invalid queue item.'));
            return $resultRedirect;
        }
        if ($this->queueManager->retryFailedJob($queueId)) {
            $this->flashMessagesManager->success(__('Queue item #%1 has been put back in queue for retry.', $queueId));
        } else {
            $this->flashMessagesManager->error(__('Queue item not found or is not in Failed status.'));
        }
        return $resultRedirect;
    }
}
