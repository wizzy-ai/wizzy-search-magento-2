<?php

namespace Wizzy\Search\Setup;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Wizzy\Search\Helpers\DB\WizzyTables;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Wizzy\Search\Model\Admin\Source\InstantSearchBehaviours;
use Wizzy\Search\Model\Admin\Source\PaginationType;
use Wizzy\Search\Services\Store\StoreManager;

class InstallSchema implements InstallSchemaInterface {

   private $defaultConfigs = [
      'wizzy_general_configuration/general_configuration/enable_sync' => '1',
      'wizzy_general_configuration/general_configuration/enable_autocomplete' => '0',
      'wizzy_general_configuration/general_configuration/enable_instant_search' => '0',
      'wizzy_general_configuration/general_configuration/instant_search_behavior' => InstantSearchBehaviours::SEARCH_AS_YOU_TYPE,
      'wizzy_general_configuration/general_configuration/replace_category_page' => '0',

      'wizzy_search_form_configuration/search_input_configuration/search_input_placeholder' => 'Search entire store here...',

      'wizzy_autocomplete_configuration/autocomplete_menu/suggestions_count' => '10',
      'wizzy_autocomplete_configuration/autocomplete_menu/categories_title' => 'Categories',
      'wizzy_autocomplete_configuration/autocomplete_menu/others_title' => 'Others',
      'wizzy_autocomplete_configuration/autocomplete_menu/alignment' => 'right',
      'wizzy_autocomplete_configuration/autocomplete_menu/no_results_behaviour' => 'show_no_results_message',
      'wizzy_autocomplete_configuration/autocomplete_menu/no_results_text' => 'No results found.',
      'wizzy_autocomplete_configuration/autocomplete_top_products/show_products_suggestions' => '1',
      'wizzy_autocomplete_configuration/autocomplete_top_products/top_products_title' => 'Top Products',
      'wizzy_autocomplete_configuration/autocomplete_top_products/top_products_count' => '6',

      'wizzy_search_configuration/search_results_pagination_configuration/pagination_type' => PaginationType::INFINITE_SCROLL,
      'wizzy_search_configuration/search_results_pagination_configuration/pagination_move_to_top_widget' => '1',

      'wizzy_search_configuration/search_results_general_configuration/dom_selector' => '.columns',
      'wizzy_search_configuration/search_results_general_configuration/search_endpoint' => '/search',
      'wizzy_search_configuration/search_results_general_configuration/no_of_products' => '20',
      'wizzy_search_configuration/search_results_general_configuration/include_out_of_stock_products' => '0',

      'wizzy_search_configuration/search_results_facets_configuration/facets_configuration' => [
         /*[
            'key' => 'all',
            'label' => 'All Fields',
            'position' => 'left',
         ]*/
      ],
      'wizzy_search_configuration/search_results_facets_configuration/category_facet_display_method' => 'hierarchy',

      'wizzy_search_configuration/search_results_sorts_configuration/sorts_configuration' => [
         [
            'field' => 'relevance',
            'label' => 'Recommended',
            'order' => 'asc',
         ],
         [
            'field' => 'sellingPrice',
            'label' => 'Price Low to High',
            'order' => 'asc',
         ],
         [
            'field' => 'sellingPrice',
            'label' => 'Price High to Low',
            'order' => 'desc',
         ],
         [
            'field' => 'discountPercentage',
            'label' => 'Better Discount',
            'order' => 'desc',
         ],
         [
            'field' => 'createdAt',
            'label' => 'Recently Launched',
            'order' => 'desc',
         ],
      ],
      'wizzy_search_configuration/search_results_swatches_configuration/swatches_configuration' => [
         [
            'key' => 'colors',
         ],
         [
            'key' => 'sizes',
         ],
      ],
   ];

   private $config;
   private $storeManager;
   private $productMetadata;

   public function __construct(ConfigInterface $config, StoreManager $storeManager, ProductMetadataInterface $productMetadata) {
      $this->config = $config;
      $this->storeManager = $storeManager;
      $this->productMetadata = $productMetadata;
   }

   public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
    $setup->startSetup();
    $this->createSyncQueueTable($setup);
    $this->createEntitiesSyncTable($setup);
    $this->setDefaultConfig();
    $setup->endSetup();
  }

  private function setDefaultConfig() {
      $defaultConfigs = $this->defaultConfigs;
      $allStores = $this->storeManager->getAllStores();

      foreach ($allStores as $storeId) {
         foreach ($defaultConfigs as $path => $value) {
            $this->config->saveConfig($path, $this->getConfigValue($value), 'stores', $storeId);
         }
      }
  }

   private function getConfigValue($value) {
      $serializeMethod = 'serialize';

      if (is_array($value)) {
         $magentoVersion = $this->productMetadata->getVersion();
         if (version_compare($magentoVersion, '2.2.0-dev', '>=') === true) {
            $serializeMethod = 'json_encode';
         }
         return $serializeMethod($value);
      }

      return $value;
   }

  private function createEntitiesSyncTable(SchemaSetupInterface $setup) {
    $conn = $setup->getConnection();

    if ($conn->isTableExists(WizzyTables::$ENTITIES_SYNC_TABLE_NAME)) {
      $conn->dropTable(WizzyTables::$ENTITIES_SYNC_TABLE_NAME);
    }

    $entitiesSyncTable = $conn->newTable(WizzyTables::$ENTITIES_SYNC_TABLE_NAME)
      ->addColumn(
        'id',
        Table::TYPE_INTEGER,
        NULL,
        ['identity'=>TRUE,'unsigned'=>TRUE,'nullable'=>FALSE,'primary'=>TRUE],
        'Unique Identifier'
      )
      ->addColumn(
        'entity_id',
        Table::TYPE_INTEGER,
        NULL,
        ['unsigned'=>TRUE,'nullable'=>FALSE],
        'Entity ID'
      )
      ->addColumn(
        'entity_type',
        Table::TYPE_TEXT,
        255,
        ['nullable' => FALSE, 'default' => 'product'],
        'Entity Type'
      )
      ->addColumn(
        'store_id',
        Table::TYPE_INTEGER,
        NULL,
        ['unsigned'=>TRUE,'nullable'=>FALSE],
        'Store ID'
      )
      ->addColumn(
        'status',
        Table::TYPE_INTEGER,
        NULL,
        ['nullable'=>FALSE, 'default' => 0],
        'Entity Sync Status'
      )
      ->addColumn(
        'last_synced_at',
        Table::TYPE_TIMESTAMP,
        NULL,
        ['nullable' => TRUE],
        'Timestamp when item is synced.'
      )
      ->addColumn(
        'created_at',
        Table::TYPE_TIMESTAMP,
        NULL,
        ['nullable' => TRUE, 'default' => Table::TIMESTAMP_INIT],
        'Timestamp when entity is added in sync table.'
      )
      ->addColumn(
        'updated_at',
        Table::TYPE_TIMESTAMP,
        NULL,
        ['nullable' => TRUE, 'default' => Table::TIMESTAMP_INIT_UPDATE],
        'Timestamp when entity is updated in sync table.'
      )
      ->setOption('charset','utf8');

    $conn->createTable($entitiesSyncTable);

    $conn->addIndex(
      $setup->getTable(WizzyTables::$ENTITIES_SYNC_TABLE_NAME),
      $setup->getIdxName(
        $setup->getTable(WizzyTables::$ENTITIES_SYNC_TABLE_NAME),
        ['entity_id','entity_type', 'store_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
      ),
      ['entity_id','entity_type', 'store_id'],
      \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    );

  }

  private function createSyncQueueTable(SchemaSetupInterface $setup) {
    $conn = $setup->getConnection();

    if ($conn->isTableExists(WizzyTables::$SYNC_QUEUE_TABLE_NAME)) {
      $conn->dropTable(WizzyTables::$SYNC_QUEUE_TABLE_NAME);
    }

    $wizzyTable = $conn->newTable(WizzyTables::$SYNC_QUEUE_TABLE_NAME)
      ->addColumn(
        'id',
        Table::TYPE_INTEGER,
        NULL,
        ['identity'=>TRUE,'unsigned'=>TRUE,'nullable'=>FALSE,'primary'=>TRUE],
        'Queue Identifier'
      )
      ->addColumn(
        'class',
        Table::TYPE_TEXT,
        255,
        ['nullable' => FALSE],
        'Queue Class to Execute'
      )
      ->addColumn(
        'data',
        Table::TYPE_TEXT,
        NULL,
        ['nullable' => FALSE],
        'Data to be supplied in Queue processor'
      )
      ->addColumn(
        'status',
        Table::TYPE_BOOLEAN,
        NULL,
        ['nullable' => FALSE, 'default' => FALSE],
        'Identify the item status in the queue.'
      )
      ->addColumn(
        'tries',
        Table::TYPE_INTEGER,
        NULL,
        ['nullable' => FALSE, 'default' => 0],
        'Number of times queue has been tried to process'
      )
      ->addColumn(
       'store_id',
       Table::TYPE_INTEGER,
       NULL,
       ['unsigned'=>TRUE,'nullable'=>FALSE],
       'Store ID'
      )
      ->addColumn(
        'errors',
        Table::TYPE_TEXT,
        NULL,
        ['nullable' => TRUE, 'default' => FALSE],
        'Queue Errors log'
      )
      ->addColumn(
        'queued_at',
        Table::TYPE_TIMESTAMP,
        NULL,
        ['nullable' => FALSE, 'default' => Table::TIMESTAMP_INIT],
        'Timestamp when queue entry is added.'
      )
      ->addColumn(
        'last_updated_at',
        Table::TYPE_TIMESTAMP,
        NULL,
        ['nullable' => TRUE, 'default' => Table::TIMESTAMP_INIT_UPDATE],
        'Timestamp when queue entry is updated.'
      )
      ->setOption('charset','utf8');

    $conn->createTable($wizzyTable);
  }
}
