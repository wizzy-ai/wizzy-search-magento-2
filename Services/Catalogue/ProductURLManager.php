<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Wizzy\Search\Services\DB\ConnectionManager;
use Wizzy\Search\Services\Store\ConfigManager;

class ProductURLManager
{
    protected $urlRewriteFactory;
    private $configManager;
    private $storeId;
    private $productUrlPathGenerator;
    private $connection;
    private $urlRewriteTable;
    private $productUrlRewrites;

    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        ConfigManager $configManager,
        ConnectionManager $connectionManager,
        ProductUrlPathGenerator $productUrlPathGenerator
    ) {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->configManager = $configManager;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->connection = $connectionManager->getConnection();
        $this->urlRewriteTable = $this->connection->getTableName('url_rewrite');
    }

    public function setStore($storeId)
    {
        $this->storeId = $storeId;
    }

    public function fetchUrls($products)
    {
        $this->productUrlRewrites = [];
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $query = $this->connection->select()
            ->from($this->urlRewriteTable, ['request_path', 'target_path', 'entity_id'])
            ->where('store_id = ?', $this->storeId)
            ->where('entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->where('entity_id IN (?)', $productIds);
        $data = $this->connection->fetchAssoc($query);

        foreach ($data as $productRewriteData) {
            $productId = $productRewriteData['entity_id'];
            if (!isset($this->productUrlRewrites[$productId])) {
                $this->productUrlRewrites[$productId] = [];
            }

            $this->productUrlRewrites[$productId][$productRewriteData['target_path']] =
                $productRewriteData['request_path'];
        }
    }

    public function getUrl($product)
    {
        $url = $product->getUrlModel()->getUrl($product, $this->getUrlOptions());
        $path = $this->productUrlPathGenerator->getCanonicalUrlPath($product);

        if (strpos($url, $path) !== false) {

            $productId = $product->getId();
            if (isset($this->productUrlRewrites[$productId])) {
                if (isset($this->productUrlRewrites[$productId][$path])) {
                    $requestPath = $this->productUrlRewrites[$productId][$path];
                    $path = str_replace('/', '\/', $path);
                    $url = preg_replace("/".$path.".*/", $requestPath, $url);
                }
            }
        }

        return $url;
    }

    private function getUrlOptions()
    {
        return [
            '_secure' => $this->configManager->hasToUseSecureUrls($this->storeId),
            '_nosid' => true,
        ];
    }
}
