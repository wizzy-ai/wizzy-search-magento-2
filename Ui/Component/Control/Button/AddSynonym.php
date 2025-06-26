<?php

namespace Wizzy\Search\Ui\Component\Control\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddSynonym implements ButtonProviderInterface
{
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function getButtonData(): array
    {
        $storeId = $this->context->getRequest()->getParam('store', 1);

        $url = $this->context->getUrlBuilder()->getUrl(
            'wizzy_search/synonyms/add',
            ['store' => $storeId]
        );

        return [
            'id'           => 'wizzy_add_synonym',
            'label' => __('Add new Synonym'),
            'class' => 'primary',
            'url' => $url,
            'data_attribute' => [
                'url' => $url
            ],
            'sort_order' => 10,
        ];
    }
}
