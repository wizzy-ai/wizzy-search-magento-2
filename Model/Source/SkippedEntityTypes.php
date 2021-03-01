<?php

namespace Wizzy\Search\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Wizzy\Search\Services\Model\SyncSkippedEntities;
use Wizzy\Search\Services\Queue\QueueManager;

class SkippedEntityTypes implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [
         [
            'value' => SyncSkippedEntities::ENTITY_TYPE_PRODUCT,
            'label' => 'Product',
         ],
         [
            'value' => SyncSkippedEntities::ENTITY_TYPE_PAGE,
            'label' => 'Page',
         ],
        ];
        return $options;
    }

    public function getLabel($statusValue)
    {
        $options = $this->toOptionArray();

        foreach ($options as $option) {
            if ($option['value'] == $statusValue) {
                return $option['label'];
            }
        }

        return '';
    }
}
