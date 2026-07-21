<?php

namespace Wizzy\Search\Model\Admin\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Wizzy\Search\Model\Admin\Config\GridFiltersConfigurationValidator;
use Wizzy\Search\Services\Store\StoreSearchConfig;

class GridFiltersConfiguration extends ArraySerialized
{
    /**
     * @var GridFiltersConfigurationValidator
     */
    private $validator;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        GridFiltersConfigurationValidator $validator = null
    ) {
        $this->validator = $validator ?: new GridFiltersConfigurationValidator();
        $this->scopeConfig = $config;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            throw new LocalizedException(__('Grid Filters configuration must be an array.'));
        }

        $this->setValue($this->validator->normalize($value, $this->getFacetsConfiguration()));

        return parent::beforeSave();
    }

    /**
     * Magento supplies all submitted values in the current config group as
     * fieldset_data. This keeps a same-save facets update and grid filters
     * validation consistent before either backend model is persisted.
     *
     * @return array
     */
    private function getFacetsConfiguration(): array
    {
        $fieldsetData = $this->getData('fieldset_data');
        if (is_array($fieldsetData) && array_key_exists('facets_configuration', $fieldsetData)) {
            return $this->decodeFacetsConfiguration($fieldsetData['facets_configuration']);
        }

        return $this->decodeFacetsConfiguration(
            $this->scopeConfig->getValue(
                StoreSearchConfig::WIZZY_FACETS,
                $this->getScope(),
                $this->getScopeCode()
            )
        );
    }

    /**
     * @param mixed $value
     * @return array
     */
    private function decodeFacetsConfiguration($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return [];
        }

        $facetsConfiguration = json_decode($value, true);
        if (!is_array($facetsConfiguration) || json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $facetsConfiguration;
    }
}
