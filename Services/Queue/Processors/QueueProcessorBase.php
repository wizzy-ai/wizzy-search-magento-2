<?php

namespace Wizzy\Search\Services\Queue\Processors;

abstract class QueueProcessorBase
{
    abstract public function execute(array $data, $storeId);
}
