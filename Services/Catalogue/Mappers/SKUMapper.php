<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class SKUMapper
{
    public function map($product, &$mappedProduct)
    {
        $SKUs[]  = $product->getData('sku');
        if ($product->getTypeID() == Configurable::TYPE_CODE) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                $SKUs[] = $child->getData('sku');
            }
        }
        $mappedProduct['sku'] = $SKUs;
    }
}
