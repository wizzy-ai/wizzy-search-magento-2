<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;

class Pages {

   private $wizzyAPIWrapper;

   public function __construct(WizzyAPIWrapper $wizzyAPIWrapper) {
      $this->wizzyAPIWrapper = $wizzyAPIWrapper;
   }

   public function save($pages, $storeId) {
      $response = $this->wizzyAPIWrapper->savePages($pages, $storeId);
      if ($response->getStatus()) {
         return TRUE;
      }
      else {
         // Log the error.
         return FALSE;
      }
   }

   public function get($storeId) {
      $response = $this->wizzyAPIWrapper->getPages($storeId);
      if ($response->getStatus()) {
         return $response['payload']['response']['payload']['pages'];
      }
      else {
         // Log the error.
         return FALSE;
      }
   }

   public function delete($pages, $storeId) {
      $response = $this->wizzyAPIWrapper->deletePages($pages, $storeId);
      if ($response->getStatus()) {
         return TRUE;
      }
      else {
         // Log the error.
         return FALSE;
      }
   }

}