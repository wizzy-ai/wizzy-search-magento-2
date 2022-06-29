<?php

namespace Wizzy\Search\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Wizzy\Search\Services\Setup\SetupUtils;
use Wizzy\Search\Model\Admin\Source\InstantSearchBehaviours;
use Wizzy\Search\Model\Admin\Source\PaginationType;
use Wizzy\Search\Services\Setup\Version125;
use Wizzy\Search\Services\Setup\Version130;
use Wizzy\Search\Services\Setup\Version135;
use Wizzy\Search\Services\Setup\Version1316;
use Wizzy\Search\Services\Setup\Version1317;

class DefaultConfigs implements DataPatchInterface
{
    private $defaultConfigs = [
        'wizzy_general_configuration/general_configuration/enable_sync' => '1',
        'wizzy_general_configuration/general_configuration/enable_autocomplete' => '0',
        'wizzy_general_configuration/general_configuration/enable_instant_search' => '0',
        'wizzy_general_configuration/general_configuration/instant_search_behavior' =>
           InstantSearchBehaviours::SEARCH_AS_YOU_TYPE,
        'wizzy_general_configuration/general_configuration/replace_category_page' => '0',
  
        'wizzy_search_form_configuration/search_input_configuration/search_input_placeholder' =>
           'Search entire store here...',
  
        'wizzy_autocomplete_configuration/autocomplete_menu/suggestions_count' => '10',
        'wizzy_autocomplete_configuration/autocomplete_menu/categories_title' => 'Categories',
        'wizzy_autocomplete_configuration/autocomplete_menu/others_title' => 'Others',
        'wizzy_autocomplete_configuration/autocomplete_menu/alignment' => 'right',
        'wizzy_autocomplete_configuration/autocomplete_menu/no_results_behaviour' => 'show_no_results_message',
        'wizzy_autocomplete_configuration/autocomplete_menu/no_results_text' => 'No results found.',
        'wizzy_autocomplete_configuration/autocomplete_top_products/show_products_suggestions' => '1',
        'wizzy_autocomplete_configuration/autocomplete_top_products/top_products_title' => 'Top Products',
        'wizzy_autocomplete_configuration/autocomplete_top_products/top_products_count' => '6',
  
        'wizzy_search_configuration/search_results_pagination_configuration/pagination_type' =>
           PaginationType::INFINITE_SCROLL,
        'wizzy_search_configuration/search_results_pagination_configuration/pagination_move_to_top_widget' => '1',
  
        'wizzy_search_configuration/search_results_general_configuration/dom_selector' => '.columns',
        'wizzy_search_configuration/search_results_general_configuration/search_endpoint' => '/search',
        'wizzy_search_configuration/search_results_general_configuration/no_of_products' => '20',
        'wizzy_search_configuration/search_results_general_configuration/display_add_to_cart_button' => '1',
        'wizzy_search_configuration/search_results_general_configuration/display_add_to_wishlist_button' => '1',
  
        'wizzy_search_configuration/search_results_facets_configuration/category_facet_display_method' => 'hierarchy',
      ];

      private $setupUtils;
      private $version125;
      private $version130;
      private $version135;
      private $version1316;
      private $version1317;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SetupUtils $setupUtils,
        Version125 $version125,
        Version130 $version130,
        Version135 $version135,
        Version1316 $version1316,
        Version1317 $version1317
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->setupUtils = $setupUtils;
        $this->version125 = $version125;
        $this->version130 = $version130;
        $this->version135 = $version135;
        $this->version1316 = $version1316;
        $this->version1317 = $version1317;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->setDefaultConfig();
        $this->version125->update();
        $this->version130->update();
        $this->version135->update();
        $this->version1316->update();
        $this->version1317->update();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function setDefaultConfig()
    {
        $defaultConfigs = $this->defaultConfigs;
        $this->setupUtils->setDefaultConfig($defaultConfigs);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
