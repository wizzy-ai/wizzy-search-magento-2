<?php
namespace Wizzy\Search\Model\Admin\Source;

use \Magento\InventoryApi\Api\SourceRepositoryInterface;

class SourceList
{
    private $sourceRepository;
    public function __construct(SourceRepositoryInterface $sourceRepository)
    {
        $this->sourceRepository = $sourceRepository;
    }
    public function tooptionArray()
    {
        $sourceData = $this->sourceRepository->getList();
        $sourceList = $sourceData->getItems();
        foreach ($sourceList as $source) {
            $data[] = [
                'value' => $source['source_code'],
                'label' => $source['name']
                ];
        }
        return $data;
    }
}
