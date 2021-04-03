<?php

namespace Wizzy\Search\Services\Store;

class StoreCatalogueConfig
{
    private $configManager;

    const WIZZY_CATALOGUE_CONFIGURATION = "wizzy_catalogue_configuration";

  // BRAND CONFIGURATION SETTINGS
    const CATALOGUE_CONFIGURATION_BRANDS = self::WIZZY_CATALOGUE_CONFIGURATION . "/catalogue_configuration_brands";

    const IS_MULTI_BRAND_STORE = self::CATALOGUE_CONFIGURATION_BRANDS . "/is_multi_brand_store";
    const BRAND_IDENTIFIABLE_BY = self::CATALOGUE_CONFIGURATION_BRANDS . "/brand_identifiable_by";

    const BRAND_IDENTIFIABLE_CATEGORIES_WAY =
       self::CATALOGUE_CONFIGURATION_BRANDS . "/brand_identifiable_categories_way";
    const BRAND_IDENTIFIABLE_CATEGORIES_LEVEL =
       self::CATALOGUE_CONFIGURATION_BRANDS . "/brand_identifiable_categories_level";
    const BRAND_IDENTIFIABLE_CATEGORIES_SELECTION =
       self::CATALOGUE_CONFIGURATION_BRANDS . "/brand_identifiable_categories_selection";
    const BRAND_IDENTIFIABLE_SUB_CATEGORIES_SELECTION =
       self::CATALOGUE_CONFIGURATION_BRANDS . "/brand_identifiable_sub_categories_selection";
    const BRAND_IDENTIFIABLE_ATTRIBUTE_SELECTION =
       self::CATALOGUE_CONFIGURATION_BRANDS . "/brand_identifiable_attribute_selection";
    const BRAND_IS_MANDATORY_FOR_SYNC =
       self::CATALOGUE_CONFIGURATION_BRANDS . "/is_brand_mandatory_for_sync";

  // GENDER CONFIGURATION SETTINGS
    const CATALOGUE_CONFIGURATION_GENDER = self::WIZZY_CATALOGUE_CONFIGURATION . "/catalogue_configuration_genders";

    const IS_MULTI_GENDER_STORE = self::CATALOGUE_CONFIGURATION_GENDER . "/has_miltiple_gender_products";
    const GENDER_IDENTIFIABLE_BY = self::CATALOGUE_CONFIGURATION_GENDER . "/genders_identifiable_by";

    const GENDER_IDENTIFIABLE_CATEGORIES = self::CATALOGUE_CONFIGURATION_GENDER . "/genders_identifiable_by_categories";
    const GENDER_IDENTIFIABLE_ATTRIBUTES = self::CATALOGUE_CONFIGURATION_GENDER . "/genders_identifiable_by_attributes";

    const GENDER_IDENTIFIABLE_CATEGORIES_PARENT_CONSIDERATION =
       self::CATALOGUE_CONFIGURATION_GENDER . "/genders_identifiable_by_categories_parent_consideration";

  // COLOR VARIABLE CONFIGURATION
    const CATALOGUE_CONFIGURATION_COLORS = self::WIZZY_CATALOGUE_CONFIGURATION . "/catalogue_configuration_colors";

    const IS_COLORS_VARIABLE_PRODUCTS = self::CATALOGUE_CONFIGURATION_COLORS . "/has_color_variable_products";
    const COLORS_IDENTIFIABLE_ATTRIBUTES =
       self::CATALOGUE_CONFIGURATION_COLORS . "/colors_identifiable_attributes_selection";

  // SIZE VARIABLE CONFIGURATION
    const CATALOGUE_CONFIGURATION_SIZES = self::WIZZY_CATALOGUE_CONFIGURATION . "/catalogue_configuration_sizes";

    const IS_SIZES_VARIABLE_PRODUCTS = self::CATALOGUE_CONFIGURATION_SIZES . "/has_size_variable_products";
    const SIZES_IDENTIFIABLE_ATTRIBUTES =
       self::CATALOGUE_CONFIGURATION_SIZES . "/sizes_identifiable_attributes_selection";

   // PRICES CONFIGURATION
    const CATALOGUE_CONFIGURATION_PRICES = self::WIZZY_CATALOGUE_CONFIGURATION . "/catalogue_configuration_prices";

    const HAS_TO_USE_MSRP_AS_ORIGINAL_PRICE = self::CATALOGUE_CONFIGURATION_PRICES . "/use_msrp_as_original_price";

    private $storeId;

    private $cachedGnderIdentityConsiderParentCategories;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
        $this->storeId = "";
        $this->cachedGnderIdentityConsiderParentCategories = [];
    }

    public function setStore($storeId)
    {
        $this->storeId = $storeId;
    }

    public function colorIdentityAttributes()
    {
        return $this->configManager->getStoreConfig(self::COLORS_IDENTIFIABLE_ATTRIBUTES, $this->storeId);
    }

    public function sizeIdentityAttributes()
    {
        return $this->configManager->getStoreConfig(self::SIZES_IDENTIFIABLE_ATTRIBUTES, $this->storeId);
    }

    public function hasColorVariableProducts()
    {
        return ($this->configManager->getStoreConfig(self::IS_COLORS_VARIABLE_PRODUCTS, $this->storeId)) ? true : false;
    }

    public function hasSizeVariableProducts()
    {
        return ($this->configManager->getStoreConfig(self::IS_SIZES_VARIABLE_PRODUCTS, $this->storeId)) ? true : false;
    }

    public function isMultiGenderStore()
    {
        return ($this->configManager->getStoreConfig(self::IS_MULTI_GENDER_STORE, $this->storeId)) ? true : false;
    }

    public function identifyGenderBy()
    {
        return $this->configManager->getStoreConfig(self::GENDER_IDENTIFIABLE_BY, $this->storeId);
    }

    public function genderIdentityCategories()
    {
        return $this->configManager->getStoreConfig(self::GENDER_IDENTIFIABLE_CATEGORIES, $this->storeId);
    }

    public function genderIdentityAttributes()
    {
        return $this->configManager->getStoreConfig(self::GENDER_IDENTIFIABLE_ATTRIBUTES, $this->storeId);
    }

    public function genderIdentityConsiderParentCategories()
    {
        if (isset($this->cachedGnderIdentityConsiderParentCategories[$this->storeId])) {
            return $this->cachedGnderIdentityConsiderParentCategories[$this->storeId];
        }
        $this->cachedGnderIdentityConsiderParentCategories[$this->storeId] = (
           $this->configManager->getStoreConfig(
               self::GENDER_IDENTIFIABLE_CATEGORIES_PARENT_CONSIDERATION,
               $this->storeId
           )
        ) ? true : false;

        return $this->cachedGnderIdentityConsiderParentCategories[$this->storeId];
    }

    public function isMultiBrandStore()
    {
        return ($this->configManager->getStoreConfig(self::IS_MULTI_BRAND_STORE, $this->storeId)) ? true : false;
    }

    public function idetifyBrandBy()
    {
        return $this->configManager->getStoreConfig(self::BRAND_IDENTIFIABLE_BY, $this->storeId);
    }

    public function brandsIdentityCategoriesWay()
    {
        return $this->configManager->getStoreConfig(self::BRAND_IDENTIFIABLE_CATEGORIES_WAY, $this->storeId);
    }

    public function brandsIdentityCategoriesLevel()
    {
        return $this->configManager->getStoreConfig(self::BRAND_IDENTIFIABLE_CATEGORIES_LEVEL, $this->storeId);
    }

    public function brandsIdentityCategories()
    {
        return $this->configManager->getStoreConfig(self::BRAND_IDENTIFIABLE_CATEGORIES_SELECTION, $this->storeId);
    }

    public function brandsIdentitySubCategories()
    {
        return $this->configManager->getStoreConfig(self::BRAND_IDENTIFIABLE_SUB_CATEGORIES_SELECTION, $this->storeId);
    }

    public function brandsIdentityAttributes()
    {
        return $this->configManager->getStoreConfig(self::BRAND_IDENTIFIABLE_ATTRIBUTE_SELECTION, $this->storeId);
    }

    public function isBrandMandatoryForSync()
    {
        return ($this->configManager->getStoreConfig(self::BRAND_IS_MANDATORY_FOR_SYNC, $this->storeId)) ? true : false;
    }

    public function hasToUseMsrpAsOriginalPrice()
    {
        return ($this->configManager->getStoreConfig(
            self::HAS_TO_USE_MSRP_AS_ORIGINAL_PRICE,
            $this->storeId
        )
        ) ? true : false;
    }
}
