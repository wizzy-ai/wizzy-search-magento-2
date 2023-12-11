<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Model\Indexer\RecoverDeletedProducts;

class Clear extends Action
{
    private $queueManager;
    private $flashMessagesManager;
    private $recoverDeletedProducts;

   /**
    * @param Context $context
    * @param QueueManager $queueManager
    * @param FlashMessagesManager $flashMessagesManager
    */
    public function __construct(
        Context $context,
        QueueManager $queueManager,
        FlashMessagesManager $flashMessagesManager,
        RecoverDeletedProducts $recoverDeletedProducts
    ) {
        parent::__construct($context);
        $this->queueManager = $queueManager;
        $this->flashMessagesManager = $flashMessagesManager;
        $this->recoverDeletedProducts = $recoverDeletedProducts;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/status');
        $jobs = $this->queueManager->clearAll();
        $jobs = count($jobs);

        if ($jobs > 0) {
            $this->flashMessagesManager->success(
                'Sync status of ' . $jobs . ' processors has been updated successfully.'
            );
        } else {
            $this->flashMessagesManager->warning('No processor in queue at the moment.');
        }
        $this->recoverDeletedProducts->executeFull();
        return $resultRedirect;
    }
}
