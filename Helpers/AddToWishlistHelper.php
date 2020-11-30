<?php

namespace Wizzy\Search\Helpers;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class AddToWishlistHelper
{

   private $postHelper;
   private $escaper;

   public function __construct(PostHelper $postHelper)
   {
      $this->postHelper = $postHelper;
      $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
   }

   public function getAddParams(UrlInterface $urlBuilder)
   {
      $params = [
         'product' => 0,
      ];
      $url = $urlBuilder->getUrl('wishlist/index/add');
      $params['product'] = 0;

      $addParams = $this->postHelper->getPostData(
         $this->escaper->escapeUrl($url),
         $params
      );

      return json_decode($addParams, TRUE);
   }
}
