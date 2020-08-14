<?php

namespace Wizzy\Search\Services\Config;

use Wizzy\Search\Services\Indexer\IndexerManager;

class WizzyCredentials {

   private $indexerManager;
   public function __construct(IndexerManager $indexerManager) {
      $this->indexerManager = $indexerManager;
   }

   public function onCredentialsSet() {
      $this->indexerManager->getCurrenciesIndexer()->reindexList([]);
   }
}