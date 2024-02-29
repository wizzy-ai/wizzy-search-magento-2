<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DebugSyncButton extends Field
{
    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * DebugSyncButton constructor.
     *
     * @param BackendSession $backendSession
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        BackendSession $backendSession,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->backendSession = $backendSession;
        parent::__construct($context, $data);
    }

    /**
     * Set template to button renderer
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);

        $url = $this->getUrl('wizzy_search/sync/debug');
        $apiResponse = $this->backendSession->getData('api_response');
        if ($apiResponse) {
            $html = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )
                ->setType('button')
                ->setClass('primary')
                ->setLabel('View Debug Result')
                ->setOnClick("setLocation('$url')")
                ->toHtml();
        } else {
            $html = '';
        }

        return $html;
    }
}
