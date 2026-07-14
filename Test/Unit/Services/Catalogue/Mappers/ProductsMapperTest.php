<?php

namespace Wizzy\Search\Test\Unit\Services\Catalogue\Mappers;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Wizzy\Search\Services\Catalogue\Mappers\ProductsMapper;

class ProductsMapperTest extends TestCase
{
    /**
     * @dataProvider supplementalPreparationDisabledProvider
     */
    public function testSupplementalPreparationIsSkippedWhenNotConfigured($enabled, array $attributeIds): void
    {
        $configurable = new ProductsMapperConfigurableStub([]);
        $storage = new ProductsMapperStorageStub([]);
        $subject = $this->createSubject($enabled, $attributeIds, $configurable, $storage);

        $this->assertSame([], $this->getSupplementalProducts($subject, [new ProductsMapperProductStub(1)]));
        $this->assertSame(0, $configurable->getParentIdsCallCount());
        $this->assertSame(0, $storage->getByIdsCallCount());
    }

    public function testSelectedParentIsPreparedAsSupplementalOnly(): void
    {
        $child = new ProductsMapperProductStub(1);
        $parent = new ProductsMapperProductStub(2);
        $configurable = new ProductsMapperConfigurableStub([1 => [2]]);
        $storage = new ProductsMapperStorageStub([2 => $parent]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertSame([$parent], $this->getSupplementalProducts($subject, [$child]));
    }

    public function testPrimaryParentIsNotPreparedAgainAsSupplemental(): void
    {
        $child = new ProductsMapperProductStub(1);
        $parent = new ProductsMapperProductStub(2);
        $configurable = new ProductsMapperConfigurableStub([1 => [2]]);
        $storage = new ProductsMapperStorageStub([2 => $parent]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertSame([], $this->getSupplementalProducts($subject, [$child, $parent]));
    }

    public function testResolvedParentIsMemoizedForTheBatch(): void
    {
        $child = new ProductsMapperProductStub(1);
        $parent = new ProductsMapperProductStub(2);
        $configurable = new ProductsMapperConfigurableStub([1 => [2]]);
        $storage = new ProductsMapperStorageStub([2 => $parent]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertSame($parent, $this->getParentProduct($subject, $child));
        $this->assertSame($parent, $this->getParentProduct($subject, $child));
        $this->assertSame(1, $configurable->getParentIdsCallCount());
        $this->assertSame(1, $storage->getByIdsCallCount());
    }

    public function testMissingParentIsMemoizedForTheBatch(): void
    {
        $child = new ProductsMapperProductStub(1);
        $configurable = new ProductsMapperConfigurableStub([1 => []]);
        $storage = new ProductsMapperStorageStub([]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertNull($this->getParentProduct($subject, $child));
        $this->assertNull($this->getParentProduct($subject, $child));
        $this->assertSame(1, $configurable->getParentIdsCallCount());
        $this->assertSame(0, $storage->getByIdsCallCount());
    }

    public function testParentMemoIsResetBetweenMapAllCalls(): void
    {
        $child = new ProductsMapperProductStub(1);
        $firstParent = new ProductsMapperProductStub(2);
        $secondParent = new ProductsMapperProductStub(3);
        $configurable = new ProductsMapperConfigurableStub([1 => [2]]);
        $storage = new ProductsMapperStorageStub([2 => $firstParent]);
        $subject = $this->createMapAllSubject($configurable, $storage);

        $subject->mapAll([], [], [], 1);
        $this->assertSame($firstParent, $this->getParentProduct($subject, $child));

        $configurable->setParentIdsByChild([1 => [3]]);
        $storage->setProductsById([3 => $secondParent]);
        $subject->mapAll([], [], [], 1);

        $this->assertSame($secondParent, $this->getParentProduct($subject, $child));
        $this->assertSame(2, $configurable->getParentIdsCallCount());
    }

    public function testEnclosingConfigurableSuppliesInheritanceWithoutRelationshipLookup(): void
    {
        $attribute = new ProductsMapperAttributeStub(10, 'material');
        $child = new ProductsMapperProductStub(1, [$attribute]);
        $enclosingConfigurable = new ProductsMapperProductStub(20, [], [$child]);
        $configurable = new ProductsMapperConfigurableStub([1 => [10]]);
        $storage = new ProductsMapperStorageStub([1 => $child]);
        $subject = $this->createChildDataMappingSubject(
            $configurable,
            $storage,
            [1 => 'child value', 20 => 'known parent value']
        );
        $mappedProduct = [
            'inStock' => true,
            'isSearchable' => false,
            'isVisibleInCatalog' => false,
            'finalPrice' => 0,
            'sellingPrice' => 0,
        ];

        $this->mapChildData($subject, $enclosingConfigurable, $mappedProduct);

        $this->assertSame(
            ['known parent value'],
            $mappedProduct['attributes'][10]['values'][0]['value']
        );
        $this->assertSame(0, $configurable->getParentIdsCallCount());
        $this->assertSame(1, $storage->getByIdsCallCount());
        $this->assertSame([[1]], $storage->getRequestedIds());
    }

    public function testStandaloneResolutionChoosesLowestNumericAvailableParentId(): void
    {
        $child = new ProductsMapperProductStub(1);
        $lowestParent = new ProductsMapperProductStub(10);
        $otherParent = new ProductsMapperProductStub(20);
        $configurable = new ProductsMapperConfigurableStub([1 => [20, 10, 20]]);
        $storage = new ProductsMapperStorageStub([20 => $otherParent, 10 => $lowestParent]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertSame($lowestParent, $this->getParentProduct($subject, $child));
        $this->assertSame([[10, 20]], $storage->getRequestedIds());

        $configurable = new ProductsMapperConfigurableStub([1 => [10, 20]]);
        $storage = new ProductsMapperStorageStub([20 => $otherParent, 10 => $lowestParent]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertSame($lowestParent, $this->getParentProduct($subject, $child));
    }

    public function testStandaloneResolutionUsesNextAvailableParent(): void
    {
        $child = new ProductsMapperProductStub(1);
        $availableParent = new ProductsMapperProductStub(20);
        $configurable = new ProductsMapperConfigurableStub([1 => [20, 10]]);
        $storage = new ProductsMapperStorageStub([20 => $availableParent]);
        $subject = $this->createSubject(true, [10], $configurable, $storage);

        $this->assertSame($availableParent, $this->getParentProduct($subject, $child));
        $this->assertSame([[10, 20]], $storage->getRequestedIds());
    }

    public function testUnavailableParentsPreserveChildFallback(): void
    {
        $attribute = new ProductsMapperAttributeStub(10, 'material');
        $child = new ProductsMapperProductStub(1, [$attribute]);
        $configurable = new ProductsMapperConfigurableStub([1 => [10, 20]]);
        $storage = new ProductsMapperStorageStub([]);
        $subject = $this->createAttributeMappingSubject(
            $configurable,
            $storage,
            [1 => 'child value']
        );
        $mappedProduct = [];

        $this->mapAttributes($subject, $child, $mappedProduct);

        $this->assertSame(
            ['child value'],
            $mappedProduct['attributes'][10]['values'][0]['value']
        );
        $this->assertNull($this->getParentProduct($subject, $child));
        $this->assertSame(1, $configurable->getParentIdsCallCount());
        $this->assertSame(1, $storage->getByIdsCallCount());
        $this->assertSame([[10, 20]], $storage->getRequestedIds());
    }

    public function testCachePreparationAttributeInheritanceAndParentMappingUseSameParent(): void
    {
        $attribute = new ProductsMapperAttributeStub(10, 'material');
        $child = new ProductsMapperProductStub(1, [$attribute]);
        $selectedParent = new ProductsMapperProductStub(10);
        $otherParent = new ProductsMapperProductStub(20);
        $configurable = new ProductsMapperConfigurableStub([1 => [20, 10]]);
        $storage = new ProductsMapperStorageStub([20 => $otherParent, 10 => $selectedParent]);
        $subject = $this->createAttributeMappingSubject(
            $configurable,
            $storage,
            [1 => 'child value', 10 => 'parent value', 20 => 'other parent value']
        );

        $this->assertSame([$selectedParent], $this->getSupplementalProducts($subject, [$child]));

        $mappedProduct = [];
        $this->mapAttributes($subject, $child, $mappedProduct);
        $this->assertSame(
            ['parent value'],
            $mappedProduct['attributes'][10]['values'][0]['value']
        );

        $mappedProduct['mainImage'] = 'child.jpg';
        $this->mapParentProduct($subject, $child, $mappedProduct);
        $this->assertSame(10, $mappedProduct['groupId']);
        $this->assertSame('product/10', $mappedProduct['url']);
        $this->assertSame(1, $configurable->getParentIdsCallCount());
    }

    public static function supplementalPreparationDisabledProvider(): array
    {
        return [
            'feature disabled' => [false, [10]],
            'no selected attributes' => [true, []],
        ];
    }

    private function createSubject($enabled, array $attributeIds, $configurable, $storage)
    {
        $reflection = new ReflectionClass(ProductsMapper::class);
        $subject = $reflection->newInstanceWithoutConstructor();
        $this->setProperty($reflection, $subject, 'isChildAttributesUseParentValueEnabled', $enabled);
        $this->setProperty($reflection, $subject, 'childAttributesUseParentValue', array_flip($attributeIds));
        $this->setProperty($reflection, $subject, 'configurable', $configurable);
        $this->setProperty($reflection, $subject, 'productsSessionStorage', $storage);
        $this->setProperty($reflection, $subject, 'parentProductsByChildId', []);

        return $subject;
    }

    private function createAttributeMappingSubject($configurable, $storage, array $valuesByProductId)
    {
        $subject = $this->createSubject(true, [10], $configurable, $storage);
        $reflection = new ReflectionClass(ProductsMapper::class);
        $this->setProperty($reflection, $subject, 'attributesToIgnore', []);
        $this->setProperty(
            $reflection,
            $subject,
            'configurableProductsData',
            new ProductsMapperConfigurableProductsDataStub()
        );
        $this->setProperty($reflection, $subject, 'storeCatalogueConfig', new ProductsMapperStoreConfigStub());
        $this->setProperty(
            $reflection,
            $subject,
            'productsAttributesManager',
            new ProductsMapperAttributesCacheStub($valuesByProductId)
        );
        $this->setProperty($reflection, $subject, 'attributesManager', new ProductsMapperSwatchManagerStub());
        $this->setProperty($reflection, $subject, 'productURLManager', new ProductsMapperUrlManagerStub());
        $this->setProperty($reflection, $subject, 'productImageManager', new ProductsMapperImageManagerStub());
        $this->setProperty($reflection, $subject, 'storeId', 1);

        return $subject;
    }

    private function createMapAllSubject($configurable, $storage)
    {
        $subject = $this->createAttributeMappingSubject($configurable, $storage, []);
        $reflection = new ReflectionClass(ProductsMapper::class);
        $this->setProperty($reflection, $subject, 'productPrices', new ProductsMapperProductPricesStub());
        $this->setProperty($reflection, $subject, 'backendUrl', new ProductsMapperBackendUrlStub());
        $this->setProperty($reflection, $subject, 'syncSkippedEntities', new ProductsMapperSkippedEntitiesStub());
        $this->setProperty($reflection, $subject, 'eventManager', new ProductsMapperEventManagerStub());

        return $subject;
    }

    private function createChildDataMappingSubject($configurable, $storage, array $valuesByProductId)
    {
        $subject = $this->createAttributeMappingSubject($configurable, $storage, $valuesByProductId);
        $reflection = new ReflectionClass(ProductsMapper::class);
        $this->setProperty($reflection, $subject, 'productPrices', new ProductsMapperProductPricesStub());
        $this->setProperty($reflection, $subject, 'stockRegistry', new ProductsMapperStockRegistryStub());

        return $subject;
    }

    private function setProperty(ReflectionClass $reflection, $subject, $name, $value)
    {
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($subject, $value);
    }

    private function getSupplementalProducts($subject, array $products)
    {
        $method = new \ReflectionMethod($subject, 'getSupplementalProductsAttributeCacheContext');
        $method->setAccessible(true);

        return $method->invoke($subject, $products);
    }

    private function getParentProduct($subject, $product)
    {
        $method = new \ReflectionMethod($subject, 'getParentProduct');
        $method->setAccessible(true);

        return $method->invoke($subject, $product);
    }

    private function mapAttributes($subject, $product, array &$mappedProduct, $knownParent = null)
    {
        $method = new \ReflectionMethod($subject, 'mapAttributes');
        $method->setAccessible(true);
        $arguments = [$product, &$mappedProduct, true, true, $knownParent];
        $method->invokeArgs($subject, $arguments);
    }

    private function mapChildData($subject, $product, array &$mappedProduct)
    {
        $method = new \ReflectionMethod($subject, 'mapChildData');
        $method->setAccessible(true);
        $arguments = [$product, &$mappedProduct];
        $method->invokeArgs($subject, $arguments);
    }

    private function mapParentProduct($subject, $product, array &$mappedProduct)
    {
        $method = new \ReflectionMethod($subject, 'mapParentProduct');
        $method->setAccessible(true);
        $arguments = [$product, &$mappedProduct];
        $method->invokeArgs($subject, $arguments);
    }
}

class ProductsMapperProductStub
{
    private $id;
    private $attributes;
    private $usedProducts;

    public function __construct($id, array $attributes = [], array $usedProducts = [])
    {
        $this->id = $id;
        $this->attributes = $attributes;
        $this->usedProducts = $usedProducts;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getVisibility()
    {
        return 4;
    }

    public function getName()
    {
        return 'Product ' . $this->id;
    }

    public function getTypeID()
    {
        return count($this->usedProducts) ? 'configurable' : 'simple';
    }

    public function getTypeInstance()
    {
        return new ProductsMapperProductTypeStub($this->usedProducts);
    }

    public function isDisabled()
    {
        return false;
    }
}

class ProductsMapperProductTypeStub
{
    private $usedProducts;

    public function __construct(array $usedProducts)
    {
        $this->usedProducts = $usedProducts;
    }

    public function getUsedProducts($product)
    {
        return $this->usedProducts;
    }
}

class ProductsMapperConfigurableStub
{
    private $parentIdsByChild;
    private $getParentIdsCallCount = 0;

    public function __construct(array $parentIdsByChild)
    {
        $this->parentIdsByChild = $parentIdsByChild;
    }

    public function getParentIdsByChild($childId)
    {
        $this->getParentIdsCallCount++;
        return isset($this->parentIdsByChild[$childId]) ? $this->parentIdsByChild[$childId] : [];
    }

    public function getParentIdsCallCount()
    {
        return $this->getParentIdsCallCount;
    }

    public function setParentIdsByChild(array $parentIdsByChild)
    {
        $this->parentIdsByChild = $parentIdsByChild;
    }
}

class ProductsMapperStorageStub
{
    private $productsById;
    private $getByIdsCallCount = 0;
    private $requestedIds = [];

    public function __construct(array $productsById)
    {
        $this->productsById = $productsById;
    }

    public function getByIds(array $ids)
    {
        $this->getByIdsCallCount++;
        $this->requestedIds[] = $ids;
        $products = [];
        foreach ($ids as $id) {
            if (isset($this->productsById[$id])) {
                $products[] = $this->productsById[$id];
            }
        }

        return $products;
    }

    public function getByIdsCallCount()
    {
        return $this->getByIdsCallCount;
    }

    public function getRequestedIds()
    {
        return $this->requestedIds;
    }

    public function setProductsById(array $productsById)
    {
        $this->productsById = $productsById;
    }
}

class ProductsMapperAttributeStub
{
    private $id;
    private $code;

    public function __construct($id, $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    public function getAttributeId()
    {
        return $this->id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributeCode()
    {
        return $this->code;
    }

    public function getIsUserDefined()
    {
        return true;
    }

    public function getIsFilterableInSearch()
    {
        return false;
    }

    public function getIsSearchable()
    {
        return true;
    }

    public function getIsFilterable()
    {
        return false;
    }

    public function getFrontendInput()
    {
        return 'text';
    }

    public function getFrontendLabel()
    {
        return 'Material';
    }
}

class ProductsMapperConfigurableProductsDataStub
{
    public function setStore($storeId)
    {
    }

    public function getAttributesToIgnore($storeId)
    {
        return [];
    }

    public function getCategoriesToIgnore($storeId)
    {
        return [];
    }

    public function getAutocompleteAttributes($storeId)
    {
        return [];
    }

    public function getKeySizesCount($products)
    {
        return [];
    }

    public function getProductCategories($product)
    {
        return [];
    }

    public function getProductAttributes($product, array $attributesToIgnore)
    {
        return [];
    }

    public function getColors(array $categories, array $attributes, $storeId)
    {
        return [];
    }

    public function getSizes(array $categories, array $attributes, $storeId)
    {
        return [];
    }
}

class ProductsMapperStoreConfigStub
{
    public function setStore($storeId)
    {
    }

    public function isBrandMandatoryForSync()
    {
        return false;
    }

    public function isChildAttributesUseParentValueEnabled()
    {
        return true;
    }

    public function getChildAttributesToUseParentValue()
    {
        return [10];
    }

    public function getExtraAttributesToBeSynced()
    {
        return [];
    }

    public function hasToCreateKSAAttribute()
    {
        return false;
    }

    public function hasToReplaceChildImage()
    {
        return false;
    }

    public function hasToReplaceChildName()
    {
        return false;
    }
}

class ProductsMapperAttributesCacheStub
{
    private $valuesByProductId;

    public function __construct(array $valuesByProductId)
    {
        $this->valuesByProductId = $valuesByProductId;
    }

    public function setAttributeValues($products, $supplementalProducts = [])
    {
    }

    public function getValue($attributeId, $productId)
    {
        return isset($this->valuesByProductId[$productId]) ? $this->valuesByProductId[$productId] : null;
    }
}

class ProductsMapperSwatchManagerStub
{
    public function getSwatchDetails($product, $attribute)
    {
        return null;
    }
}

class ProductsMapperUrlManagerStub
{
    public function setStore($storeId)
    {
    }

    public function fetchUrls($products)
    {
    }

    public function getUrl($product)
    {
        return 'product/' . $product->getId();
    }
}

class ProductsMapperImageManagerStub
{
    public function getPlaceholderImage($storeId)
    {
        return 'placeholder.jpg';
    }
}

class ProductsMapperProductPricesStub
{
    public function setStore($storeId)
    {
    }

    public function getFinalPrice($product)
    {
        return 0;
    }

    public function getOriginalPrice($product)
    {
        return 0;
    }

    public function getSellingPrice($product)
    {
        return 0;
    }
}

class ProductsMapperStockRegistryStub
{
    public function getStockItem($productId)
    {
        return new ProductsMapperStockItemStub();
    }
}

class ProductsMapperStockItemStub
{
    public function getIsInStock()
    {
        return true;
    }

    public function getQty()
    {
        return 1;
    }
}

class ProductsMapperBackendUrlStub
{
    public function getUrl($routePath)
    {
        return 'https://admin.example/' . $routePath;
    }
}

class ProductsMapperSkippedEntitiesStub
{
    public function deleteSkippedEntities($ids, $storeId)
    {
    }

    public function addSkippedEntities($entities, $storeId)
    {
    }
}

class ProductsMapperEventManagerStub
{
    public function dispatch($eventName, array $data = [])
    {
    }
}
