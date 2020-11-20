<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\QueueManager;

class Clearentities extends Action
{
    private $entitiesSyncManager;
    private $flashMessagesManager;

   /**
    * @param Context $context
    * @param QueueManager $queueManager
    * @param FlashMessagesManager $flashMessagesManager
    */
    public function __construct(
        Context $context,
        EntitiesSync $entitiesSyncManager,
        FlashMessagesManager $flashMessagesManager
    ) {
        parent::__construct($context);
        $this->entitiesSyncManager = $entitiesSyncManager;
        $this->flashMessagesManager = $flashMessagesManager;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/status');
        $entities = $this->entitiesSyncManager->markEverythingSynced();
        $entities = count($entities);

        if ($entities > 0) {
            $this->flashMessagesManager->success(
                'Sync status of ' . $entities . ' entities has been updated successfully.'
            );
        } else {
            $this->flashMessagesManager->warning('No entities in sync at the moment.');
        }
        return $resultRedirect;
    }
}
