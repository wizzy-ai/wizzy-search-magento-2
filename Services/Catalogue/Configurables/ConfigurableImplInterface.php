<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

interface ConfigurableImplInterface
{
    public function getValue(array $categories, array $attributes, $storeId);
    public function getConfiguredCategories($storeId);
    public function getConfiguredAttributes($storeId);
}
