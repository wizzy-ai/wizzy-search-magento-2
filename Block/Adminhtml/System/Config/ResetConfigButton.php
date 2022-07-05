<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

use \Magento\Framework\App\Request\Http;
use \Magento\Backend\Block\Template\Context;
use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;
use \Magento\Framework\App\ProductMetadataInterface;

class ResetConfigButton extends Field
{
    protected $request;

    public function __construct(
        Http $request,
        Context $context,
        ProductMetadataInterface $productMetadata
    ) {
        $this->request = $request;
        $this->productMetadata = $productMetadata;
        return parent::__construct($context);
    }

    public function getStoreViewId()
    {
        return $this->request->getParam('store');
    }

    public function hasToShowResetConfigButton()
    {
        $currentVersion = $this->productMetadata->getVersion();
        $requiredVersion = "2.3.0";
        return version_compare($currentVersion, $requiredVersion, '>=');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate() && $this->hasToShowResetConfigButton()) {
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
