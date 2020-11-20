<?php

namespace Wizzy\Search\Helpers;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\Helper\Data as UrlDataHelper;
use Magento\Framework\UrlInterface;

class UrlHelper
{

    private $urlDataHelper;
    public function __construct(UrlDataHelper $urlDataHelper)
    {
        $this->urlDataHelper = $urlDataHelper;
    }

    public function getAddToCartAction(UrlInterface $urlBuilder, $urlToRedirect)
    {
        $redirectUrl = $this->urlDataHelper->getEncodedUrl($urlToRedirect);
        $urlEncodedName = ActionInterface::PARAM_NAME_URL_ENCODED;

        $urlParams = [
         $urlEncodedName => $redirectUrl,
         '_secure' => true,
        ];

        return $urlBuilder->getUrl('checkout/cart/add', $urlParams);
    }
}
