<?php
namespace Wizzy\Search\Block\Adminhtml\Synonym;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;
use Magento\Framework\Registry;

class Edit extends Template
{
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->registry = $registry;
    }

    public function getFormAction()
    {
        $storeId = $this->getRequest()->getParam('store', 1);
        return $this->getUrl('wizzy_search/synonyms/save', ['store' => $storeId]);
    }

    public function getSynonymData()
    {
        return $this->registry->registry('wizzy_synonym_data');
    }

    public function getBackUrl()
    {
        $storeId = $this->getRequest()->getParam('store', 1);
        return $this->getUrl('wizzy_search/synonyms/index', ['store' => $storeId]);
    }
}
