<?php
namespace Wizzy\Search\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class SynonymActions extends Column
{
    const EDIT_URL = 'wizzy_search/synonyms/edit';
    const DELETE_URL = 'wizzy_search/synonyms/delete';
    protected $urlBuilder;
    protected $url_context;

    public function __construct(
        ContextInterface $context,
        Context $url_context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->url_context = $url_context;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $storeId = $this->url_context->getRequest()->getParam('store', 1);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $editUrl = $this->urlBuilder->getUrl(self::EDIT_URL, [
                        'id' => $item['id'],
                        'store' => $storeId
                    ]);
                    $deleteUrl = $this->urlBuilder->getUrl(self::DELETE_URL, [
                        'id' => $item['id'],
                        'store' => $storeId
                    ]);
    
                    $item[$this->getData('name')] = '
                        <div class="wizzy-action-buttons" style="display:flex">
                            <a 
                            href="' . $editUrl . '" 
                            class="action-default wizzy-edit-btn" 
                            style="display:inline-block">Edit</a>'
                            . '&nbsp;&nbsp;'
                            .
                            '<a 
                            href="' . $deleteUrl . '" 
                            class="action-default wizzy-delete-btn" 
                            style="display:inline-block" 
                            onclick="return confirm(\'Are you sure you want to delete this synonym?\')">
                            Delete
                            </a>
                        </div>';
                }
            }
        }

        return $dataSource;
    }
}
