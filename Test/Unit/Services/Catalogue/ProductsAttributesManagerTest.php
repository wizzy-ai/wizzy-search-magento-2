<?php

namespace Wizzy\Search\Test\Unit\Services\Catalogue;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\TestCase;
use Wizzy\Search\Services\Catalogue\ProductsAttributesManager;

class ProductsAttributesManagerTest extends TestCase
{
    public function testSupplementalConfigurableCachesOnlyItsOwnValues(): void
    {
        $childAttribute = new ProductsAttributesManagerAttributeStub(10, 'material', 'Cotton');
        $parentAttribute = new ProductsAttributesManagerAttributeStub(10, 'material', '');
        $siblingAttribute = new ProductsAttributesManagerAttributeStub(10, 'material', 'Wool');
        $sibling = new ProductsAttributesManagerProductStub(3, 'simple', [$siblingAttribute]);
        $typeInstance = new ProductsAttributesManagerTypeInstanceStub([$sibling]);
        $child = new ProductsAttributesManagerProductStub(1, 'simple', [$childAttribute]);
        $parent = new ProductsAttributesManagerProductStub(
            2,
            Configurable::TYPE_CODE,
            [$parentAttribute],
            ['material' => 'Linen'],
            $typeInstance
        );

        $subject = new ProductsAttributesManager();
        $subject->setAttributeValues([$child], [$parent]);

        $this->assertSame('Cotton', $subject->getValue(10, 1));
        $this->assertSame('Linen', $subject->getValue(10, 2));
        $this->assertNull($subject->getValue(10, 3));
        $this->assertSame(0, $typeInstance->getUsedProductsCallCount());
    }

    public function testPrimaryConfigurableStillCachesUsedProductValues(): void
    {
        $childAttribute = new ProductsAttributesManagerAttributeStub(10, 'material', 'Cotton');
        $child = new ProductsAttributesManagerProductStub(1, 'simple', [$childAttribute]);
        $typeInstance = new ProductsAttributesManagerTypeInstanceStub([$child]);
        $parent = new ProductsAttributesManagerProductStub(
            2,
            Configurable::TYPE_CODE,
            [],
            [],
            $typeInstance
        );

        $subject = new ProductsAttributesManager();
        $subject->setAttributeValues([$parent]);

        $this->assertSame('Cotton', $subject->getValue(10, 1));
        $this->assertSame(1, $typeInstance->getUsedProductsCallCount());
    }

    public function testDuplicateSupplementalProductDoesNotOverwritePrimaryValue(): void
    {
        $primaryAttribute = new ProductsAttributesManagerAttributeStub(10, 'material', 'Primary');
        $supplementalAttribute = new ProductsAttributesManagerAttributeStub(10, 'material', 'Supplemental');
        $primary = new ProductsAttributesManagerProductStub(2, 'simple', [$primaryAttribute]);
        $supplemental = new ProductsAttributesManagerProductStub(2, 'simple', [$supplementalAttribute]);

        $subject = new ProductsAttributesManager();
        $subject->setAttributeValues([$primary], [$supplemental]);

        $this->assertSame('Primary', $subject->getValue(10, 2));
        $this->assertSame(0, $supplementalAttribute->getFrontend()->getValueCallCount());
    }

    public function testFullyLoadedPrimaryChildRawFallbackOverridesExpandedValue(): void
    {
        $expandedAttribute = new ProductsAttributesManagerAttributeStub(10, 'featured', 'No');
        $expandedChild = new ProductsAttributesManagerProductStub(1, 'simple', [$expandedAttribute]);
        $typeInstance = new ProductsAttributesManagerTypeInstanceStub([$expandedChild]);
        $parent = new ProductsAttributesManagerProductStub(
            2,
            Configurable::TYPE_CODE,
            [],
            [],
            $typeInstance
        );
        $loadedAttribute = new ProductsAttributesManagerAttributeStub(10, 'featured', '');
        $loadedChild = new ProductsAttributesManagerProductStub(
            1,
            'simple',
            [$loadedAttribute],
            ['featured' => 'Yes']
        );

        $subject = new ProductsAttributesManager();
        $subject->setAttributeValues([$parent, $loadedChild]);

        $this->assertSame('Yes', $subject->getValue(10, 1));
    }

    public function testPrimaryChildNullIsNotOverwrittenByExpandedDefaultRegardlessOfOrder(): void
    {
        $childFirstValue = $this->getPrimaryNullValueWithChildFirst(true);
        $parentFirstValue = $this->getPrimaryNullValueWithChildFirst(false);

        $this->assertNull($childFirstValue);
        $this->assertSame($childFirstValue, $parentFirstValue);
    }

    private function getPrimaryNullValueWithChildFirst($childFirst)
    {
        $expandedAttribute = new ProductsAttributesManagerAttributeStub(10, 'featured', 'No');
        $expandedChild = new ProductsAttributesManagerProductStub(1, 'simple', [$expandedAttribute]);
        $typeInstance = new ProductsAttributesManagerTypeInstanceStub([$expandedChild]);
        $parent = new ProductsAttributesManagerProductStub(
            2,
            Configurable::TYPE_CODE,
            [],
            [],
            $typeInstance
        );
        $loadedAttribute = new ProductsAttributesManagerAttributeStub(10, 'featured', '');
        $loadedChild = new ProductsAttributesManagerProductStub(
            1,
            'simple',
            [$loadedAttribute],
            ['featured' => null]
        );

        $subject = new ProductsAttributesManager();
        $products = $childFirst ? [$loadedChild, $parent] : [$parent, $loadedChild];
        $subject->setAttributeValues($products);

        return $subject->getValue(10, 1);
    }
}

class ProductsAttributesManagerProductStub
{
    private $id;
    private $typeId;
    private $attributes;
    private $data;
    private $typeInstance;

    public function __construct($id, $typeId, array $attributes, array $data = [], $typeInstance = null)
    {
        $this->id = $id;
        $this->typeId = $typeId;
        $this->attributes = $attributes;
        $this->data = $data;
        $this->typeInstance = $typeInstance;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTypeID()
    {
        return $this->typeId;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function getTypeInstance()
    {
        return $this->typeInstance;
    }
}

class ProductsAttributesManagerAttributeStub
{
    private $id;
    private $code;
    private $frontend;

    public function __construct($id, $code, $frontendValue)
    {
        $this->id = $id;
        $this->code = $code;
        $this->frontend = new ProductsAttributesManagerFrontendStub($frontendValue);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributeCode()
    {
        return $this->code;
    }

    public function getFrontend()
    {
        return $this->frontend;
    }
}

class ProductsAttributesManagerFrontendStub
{
    private $value;
    private $getValueCallCount = 0;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue($product)
    {
        $this->getValueCallCount++;
        return $this->value;
    }

    public function getValueCallCount()
    {
        return $this->getValueCallCount;
    }
}

class ProductsAttributesManagerTypeInstanceStub
{
    private $usedProducts;
    private $getUsedProductsCallCount = 0;

    public function __construct(array $usedProducts)
    {
        $this->usedProducts = $usedProducts;
    }

    public function getUsedProducts($product)
    {
        $this->getUsedProductsCallCount++;
        return $this->usedProducts;
    }

    public function getUsedProductsCallCount()
    {
        return $this->getUsedProductsCallCount;
    }
}
