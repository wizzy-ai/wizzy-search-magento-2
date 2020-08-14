<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

interface ConfigurableImpl {
  public function getValue(array $categories, array $attributes, $storeId);
  public function getConfiguredCategories($storeId);
  public function getConfiguredAttributes($storeId);
}