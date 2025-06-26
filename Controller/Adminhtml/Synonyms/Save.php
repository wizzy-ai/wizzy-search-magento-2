<?php

namespace Wizzy\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Magento\Backend\App\Action\Context;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
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
        $data = $this->getRequest()->getPostValue();
        $storeId = $this->getRequest()->getParam('store') ?? null;
        if (!$storeId) {
            $this->flashMessagesManager->error('Please select the store view to perform this action.');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index', ['store' => $storeId]);

        $payload = [
            'keyword' => $data['keyword'],
            'type' => $data['type'],
            'relatedWords' =>  $data['relatedWords']
        ];

        if (!empty($data['id'])) {
            $payload['id'] = $data['id'];
            $response = $this->wizzyApiWrapper->editSynonyms($storeId, $payload);

            $statusCode = $response['payload']['response']['statusCode'] ?? null;
            if ($statusCode != 200) {
                $this->flashMessagesManager->error('Error occurred while editing the synonym.');
                return $resultRedirect;
            }
            $this->flashMessagesManager->success(
                'Synonym has been edited successfully.'
            );
        } else {
            $response = $this->wizzyApiWrapper->addSynonyms($storeId, $payload);
            
            $statusCode = $response['payload']['response']['statusCode'] ?? null;
            if ($statusCode != 200) {
                $this->flashMessagesManager->error('Error occurred while adding the synonym.');
                return $resultRedirect;
            }
            $this->flashMessagesManager->success(
                'Synonym has been added successfully.'
            );
        }

        return $resultRedirect;
    }
}
