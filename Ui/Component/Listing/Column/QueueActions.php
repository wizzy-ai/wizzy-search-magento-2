<?php

namespace Wizzy\Search\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class QueueActions extends Column
{
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $actions = [];

                // View action
                $actions['view'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'wizzy_search/queue/view',
                        ['id' => $item['id']]
                    ),
                    'label' => __('View'),
                ];

                // Delete action
                $actions['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'wizzy_search/queue/delete',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete Queue Item'),
                        'message' => __('Are you sure you want to delete this queue item?')
                    ],
                    'post' => true,
                ];

                $item[$this->getData('name')] = $actions;
            }
        }

        return $dataSource;
    }
}
