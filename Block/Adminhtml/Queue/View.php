<?php

namespace Wizzy\Search\Block\Adminhtml\Queue;

use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Wizzy\Search\Model\Source\QueueProcessors;
use Wizzy\Search\Model\Source\QueueStatuses;
use Wizzy\Search\Services\Queue\QueueManager;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Date;

class View extends Template
{
    private $queueManager;
    private $systemStore;
    private $escaper;
    private $queueProcessors;
    private $queueStatues;
    private $timezone;

   /**
    * @param Context       $context
    * @param QueueManager $queueManager
    * @param SystemStore $systemStore
    * @param Escaper $escaper
    * @param QueueStatuses $queueStatuse
    * @param QueueProcessors $queueProcessors,
    * @param TimezoneInterface $timezone,
    * @param array         $data
    */
    public function __construct(
        Context       $context,
        QueueManager  $queueManager,
        SystemStore   $systemStore,
        Escaper $escaper,
        QueueStatuses $queueStatuses,
        QueueProcessors $queueProcessors,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->queueManager = $queueManager;
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
        $this->queueStatues = $queueStatuses;
        $this->queueProcessors = $queueProcessors;
        $this->timezone = $timezone;
    }

    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(Button::class);
        $button->setData(
            [
            'label' => __('Back to Queue Processors'),
            'onclick' => 'setLocation(\'' . $this->getQueueProcessorsUrl() . '\')',
            'class' => 'back',
            ]
        );

        $this->getPageToolbar()->setChild('back_button', $button);
        return parent::_prepareLayout();
    }

    public function getQueueProcessorsUrl()
    {
        return $this->getUrl('*/*/status');
    }

    public function getPageToolbar()
    {
        return $this->getLayout()->getBlock('page.actions.toolbar');
    }

    public function getQueueProcessorItem()
    {
        $queueId = $this->getRequest()->getParam('id');
        $queueData = $this->queueManager->get($queueId);

        $queueData['store'] =
           (!$queueData['store_id']) ?
              "All Store Views" : $this->getStoreDetails($queueData['store_id']);
        $queueData['status'] = $this->queueStatues->getLabel($queueData['status']);
        $queueData['class'] = $this->queueProcessors->getLabel($queueData['class']);
        $queueData['errors'] = (!isset($queueData['errors'])) ? '-' : $queueData['errors'];
        $queueData['queued_at'] = $this->timezone->formatDateTime(
            $this->timezone->date($queueData['queued_at']),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
        $queueData['last_updated_at'] = $this->timezone->formatDateTime(
            $this->timezone->date($queueData['last_updated_at']),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );

        return $queueData;
    }

    private function getStoreDetails($storeId)
    {
        $data = $this->systemStore->getStoresStructure(false, [$storeId]);
        $content = "";
        foreach ($data as $website) {
            $content .= $website['label'] . "<br/>";
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($group['label']) . "<br/>";
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . "<br/>";
                }
            }
        }

        return $content;
    }
}
