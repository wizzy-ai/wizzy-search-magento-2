<?php

namespace Wizzy\Search\Services\Queue\Processors;

abstract class QueueProcessorBase {
  public abstract function execute(array $data, $storeId);
}