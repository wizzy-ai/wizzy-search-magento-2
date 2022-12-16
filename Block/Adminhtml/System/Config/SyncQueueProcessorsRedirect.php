<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

class SyncQueueProcessorsRedirect extends BasePageRedirect
{
    protected function _prepareLayout()
    {
        $this->setPage('wizzy_search/queue/status');
        return parent::_prepareLayout();
    }
}
