<?php
namespace Wizzy\Search\Ui\Component\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Wizzy\Search\Model\ResourceModel\Synonym\DataProvider as SynonymResourceProvider;

class SynonymDataProvider extends AbstractDataProvider
{
    protected $resourceProvider;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        SynonymResourceProvider $resourceProvider,
        array $meta = [],
        array $data = []
    ) {
        $this->resourceProvider = $resourceProvider;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $response = $this->resourceProvider->getSynonyms();
        $items = $response['payload']['response']['payload']['synonyms'] ?? [];

        foreach ($items as &$item) {
            if (isset($item['relatedWords'])) {
                $item['relatedWords'] = nl2br($item['relatedWords']);
            }
            if (isset($item['type'])) {
                if ($item['type'] === 'one-way') {
                    $item['type'] = 'One Way';
                } else {
                    $item['type'] = 'Regular';
                }
            }
        }
        
        return [
            'items' => $items,
            'totalRecords' => count($items)
        ];
    }
}
