<?php

namespace Wizzy\Search\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Wizzy\Search\Services\Queue\QueueManager;

class QueueStatuses implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [
         [
            'value' => QueueManager::JOB_TO_EXECUTE_STATUS,
            'label' => 'In Queue',
         ],
         [
            'value' => QueueManager::JOB_IN_PROGRESS_STATUS,
            'label' => 'In Progress',
         ],
         [
            'value' => QueueManager::JOB_PROCESSED_STATUS,
            'label' => 'Completed',
         ],
         [
            'value' => QueueManager::JOB_CANCELLED_STATUS,
            'label' => 'Cancelled',
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
