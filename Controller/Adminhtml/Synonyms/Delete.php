<?php

namespace Wizzy\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Magento\Backend\App\Action\Context;
use Wizzy\Search\Helpers\FlashMessagesManager;

class Delete extends Action
{
    protected $wizzyApiWrapper;
    protected $resultRedirectFactory;
    protected $context;
    private $flashMessagesManager;

    public function __construct(
        Context $context,
        WizzyAPIWrapper $wizzyApiWrapper,
        FlashMessagesManager $flashMessagesManager
    ) {
        parent::__construct($context);
        $this->wizzyApiWrapper = $wizzyApiWrapper;
        $this->context = $context;
        $this->flashMessagesManager = $flashMessagesManager;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store') ?? null;
        if (!$storeId) {
            $this->flashMessagesManager->error('Please select the store view to perform this action.');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index', ['store' => $storeId]);
        
        if (!$id) {
            $this->flashMessagesManager->error('ID is required to perform this action.');
            return $resultRedirect;
        }

        $payload = $id;
        $response = $this->wizzyApiWrapper->deleteSynonyms($storeId, $payload);

        $statusCode = $response['payload']['response']['statusCode'] ?? null;
        if ($statusCode != 200) {
            $this->flashMessagesManager->error('Error occurred while deleting the synonym.');
            return $resultRedirect;
        }
        $this->flashMessagesManager->success(
            'Synonym has been deleted successfully.'
        );
        
        return $resultRedirect;
    }
}
