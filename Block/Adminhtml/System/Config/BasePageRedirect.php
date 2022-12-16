<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;

class BasePageRedirect extends Field
{
    protected $page;
    private $urlBuilder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $context->getUrlBuilder();
    }

    protected function setPage($page) {
        $this->page = $this->urlBuilder->getUrl($page);
    }

    public function getPageRedirectUrl()
    {
        return $this->page;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/wizzy_page_redirect.phtml');
        }

        return $this;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
