<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Session\UserSessionManager;

class CheckoutObserver implements ObserverInterface
{
    private $userSessionManager;
    private $output;

    public function __construct(IndexerOutput $output, UserSessionManager $userSessionManager)
    {
        $this->userSessionManager = $userSessionManager;
        $this->output = $output;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            $order = $event->getData('order');

            $payload = [
            'items' => [

            ],
            'value' => 0,
            'qty'   => 0,
            'searchResponseId' => '',
            'id' => '',
            ];

            if ($order) {
                $payload['id'] = $order->getId();
                $items = $order->getAllVisibleItems();
                foreach ($items as $item) {
                    $item = $item->getData();

                    if (isset($item['base_row_total_incl_tax'])) {
                        $payload['value'] += $item['base_row_total_incl_tax'];
                    }
                    if (isset($item['qty_ordered'])) {
                        $payload['qty'] += $item['qty_ordered'];
                    }

                    if (isset($item['product_options']) && is_array($item['product_options']) &&
                    isset($item['product_options']['info_buyRequest']) &&
                    is_array($item['product_options']['info_buyRequest']) &&
                    isset($item['product_options']['info_buyRequest']['searchResponseId'])
                    ) {
                        $payload['searchResponseId'] = $item['product_options']['info_buyRequest']['searchResponseId'];
                    }

                    if (isset($item['product_id'])) {
                        $payload['items'][] = [
                        'itemId' => $item['product_id'],
                        'qty'    => $item['qty_ordered']
                        ];

                        if ($payload['searchResponseId'] != '') {
                            $payload['searchResponseId'] =
                               $this->userSessionManager->getResponseIdFromClicks($item['product_id']);
                        }
                    }
                }
            }

            if (count($payload['items'])) {
                $this->userSessionManager->addInQueue($payload, UserSessionManager::PRODUCTS_PURCHASED);
            }
        } catch (\Exception $exception) {
            $this->output->log([
            'Message'  => $exception->getMessage(),
            'Trace' => $exception->getTraceAsString(),
            'Class' => get_class($exception),
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            ]);
        }
    }
}
