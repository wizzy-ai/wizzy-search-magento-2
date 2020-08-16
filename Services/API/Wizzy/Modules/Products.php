<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;

class Products
{

    private $wizzyAPIWrapper;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
    }

    public function save(array $products, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveProducts($products, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
          // Log the error.
            return false;
        }
    }

    public function delete(array $products, $storeId)
    {
        $response = $this->wizzyAPIWrapper->deleteProducts($products, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
          // Log the error.
            return false;
        }
    }
}
