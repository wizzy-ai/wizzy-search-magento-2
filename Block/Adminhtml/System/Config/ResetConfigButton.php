<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

use \Magento\Framework\App\Request\Http;
use \Magento\Backend\Block\Template\Context;
use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;

class ResetConfigButton extends Field
{
    protected $request;

    public function __construct(
        Http $request,
        Context $context
    ) {
        $this->request = $request;
        return parent::__construct($context);
    }

    public function getStoreViewId()
    {
        return $this->request->getParam('store');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Wizzy_Search::system/config/reset_config_button.phtml');
        }
        return $this;
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __("Remove Credentials"),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('wizzy_search/config/resetconfig'),
            ]
        );
        
        return $this->_toHtml();
    }
}
