<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

class SkippedEntities extends BasePageRedirect
{
    protected function _prepareLayout()
    {
        $this->setConfig('wizzy_skipped_entities/page_button/redirect_page');
        $this->setPage('wizzy_search/sync/skippedentities');
        return parent::_prepareLayout();
    }
}
