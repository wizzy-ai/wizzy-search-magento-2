<?php

namespace Wizzy\Search\Ui\Component\Control\Button;

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class QueueOperations implements ButtonProviderInterface
{
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        return [
            'id'           => 'wizzy_queue_operations',
            'label'        => __('Queue Operations'),
            'class'        => 'primary',
            'button_class' => 'wizzy-queue-operations',
            'class_name'   => SplitButton::class,
            'options'      => $this->getOptions(),
            'sort_order'   => 5,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'addAllProductsForSync' => [
                'label' => __('Add all Products For Sync'),
                'default' => true,
                'onclick' => $this->getOnClickUrl('*/*/addAllProductsForSync')
            ],
            'backToQueue' => [
                'label' => __('Enqueue All In Progress'),
                'onclick' => $this->getOnClickUrl('*/*/backToQueue')
            ],
            'clearQueue' => [
                'label' => __('Clear Queue'),
                'onclick' => $this->getOnClickUrl('*/*/clear')
            ],
            'clearentities' => [
                'label' => __('Clear Entities Sync'),
                'onclick' => $this->getOnClickUrl('*/*/clearentities')
            ],
            'truncate' => [
                'label' => __('Truncate Queue'),
                'onclick' => $this->getOnClickUrl('*/*/truncate')
            ],
        ];
    }

    protected function getOnClickUrl(string $path, array $params = []): string
    {
        return sprintf(
            "setLocation('%s')",
            $this->context->getEscaper()->escapeUrl(
                $this->context->getUrlBuilder()->getUrl($path, $params)
            )
        );
    }
}
