<?php

namespace Wizzy\Search\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class SkippedEntityData extends Column
{
    const ENTITY_DATA_KEY = "entity_data";
    const URL_KEY = "URL";

    protected function prepareItem(array $item)
    {
        $content = $item[self::ENTITY_DATA_KEY];
        $data = json_decode($content, true);

        if ($data === false) {
            return $content;
        }

        $content = "<ul>";

        foreach ($data as $key => $value) {
            if ($value === 0 || $value === "") {
                if ($value === "") {
                    $value = "(empty)";
                }
                $content .= "<li><b>" . $key . "</b> : " . $value;
            }
            if ($key === self::URL_KEY && $value != "") {
               $content .= "<li><b>" . $key . "</b> : " . $value . " (Invalid URL)";
            }
        }

        $content .= "</ul>";

        return $content;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }
        return $dataSource;
    }
}
