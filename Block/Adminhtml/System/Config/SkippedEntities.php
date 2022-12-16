<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

class SkippedEntities extends BasePageRedirect
{
    protected function _prepareLayout()
    {
        $this->setPage('wizzy_search/sync/skippedentities');
        return parent::_prepareLayout();
    }
}
