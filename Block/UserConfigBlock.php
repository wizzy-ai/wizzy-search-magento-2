<?php

namespace Wizzy\Search\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

class UserConfigBlock extends Template
{
    private $customerSession;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    private function getLoggedInUserId()
    {
        $customer = $this->customerSession->getCustomer();
        return ($customer && $customer->getId()) ? $customer->getId() : '';
    }

    public function getConfigs()
    {
        $configs = [
         'loggedInUser' => [
            'id' => $this->getLoggedInUserId(),
         ]
        ];

        return $configs;
    }
}
