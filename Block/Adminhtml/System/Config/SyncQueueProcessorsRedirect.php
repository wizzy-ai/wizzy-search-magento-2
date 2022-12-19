<?php

namespace Wizzy\Search\Block\Adminhtml\System\Config;

class SyncQueueProcessorsRedirect extends BasePageRedirect
{
    protected function _prepareLayout()
    {
        $this->setConfig('wizzy_queue_processors_status/page_button/redirect_page');
        $this->setPage('wizzy_search/queue/status');
        return parent::_prepareLayout();
    }
}
