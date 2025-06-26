<?php

namespace Wizzy\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Wizzy\Search\Model\ResourceModel\Synonym\DataProvider as SynonymResourceProvider;

class Edit extends Action
{
    protected $resultRedirectFactory;
    private $flashMessagesManager;
    protected $registry;
    protected $context;
    protected $resourceProvider;

    public function __construct(
        Context $context,
        FlashMessagesManager $flashMessagesManager,
        SynonymResourceProvider $resourceProvider,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->flashMessagesManager = $flashMessagesManager;
        $this->registry = $registry;
        $this->resourceProvider = $resourceProvider;
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

        try {
            $response = $this->resourceProvider->getSynonyms();
        
            $items = $response['payload']['response']['payload']['synonyms'] ?? [];
            $synonym = null;
            foreach ($items as $item) {
                if ($item["id"] === (int)$id) {
                    $synonym = $item;
                }
            }

            $this->registry->register('wizzy_synonym_data', $synonym);

        } catch (\Exception $e) {
            $this->flashMessagesManager->error('Error occurred while editing the synonym.');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Wizzy_Search::synonyms');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Synonym'));

        return $resultPage;
    }
}
