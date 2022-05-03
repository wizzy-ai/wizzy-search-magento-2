<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Magento\Indexer\App\Indexer;
use Wizzy\Search\Services\API\Wizzy\Modules\Pages;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Store\PagesManager;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class IndexPagesProcessor extends QueueProcessorBase
{

    private $storeManager;
    private $pagesManager;
    private $storeAutocompleteConfig;
    private $pagesSaver;
    private $storeGeneralConfig;
    private $output;

    public function __construct(
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        PagesManager $pagesManager,
        StoreAutocompleteConfig $storeAutocompleteConfig,
        Pages $pages,
        IndexerOutput $output
    ) {
        $this->storeManager = $storeManager;
        $this->pagesManager = $pagesManager;
        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->pagesSaver = $pages;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->output = $output;
    }

    public function execute(array $data, $storeId)
    {
        $this->storeAutocompleteConfig->setStore($storeId);
        if ($this->storeAutocompleteConfig->hasToSyncPages() == false) {
            return true;
        }
        $storeIds = $this->storeManager->getToSyncStoreIds($storeId);
        $pages = $this->pagesManager->fetchAll();

        foreach ($storeIds as $storeId) {
            $this->storeGeneralConfig->setStore($storeId);
            $this->output->writeln(__('Processing ' .count($pages) . ' for Store #' . $storeId));

            if (!$this->storeGeneralConfig->isSyncEnabled()) {
                $this->output->writeln(__('Index Pages Processor Skipped as Sync is disabled.'));
                return true;
            }

            $this->storeAutocompleteConfig->setStore($storeId);
            $pagesToSave = $this->getPagesToSave($pages, $storeId);
            $pagesToDelete = $this->getPagesToDelete($pagesToSave, $storeId);
            if (count($pagesToSave)) {
                $this->output->writeln(__('Saving ' .count($pagesToSave) . ' Pages'));
                $response = $this->pagesSaver->save($pagesToSave, $storeId);

                if ($response) {
                    $this->output->writeln(__('Saved ' .count($pagesToSave) . ' Pages successfully'));
                }
            }
            if (count($pagesToDelete)) {
                $this->output->writeln(__('Deleting ' .count($pagesToDelete) . ' Pages'));

                $response = $this->pagesSaver->delete($pagesToDelete, $storeId);
                if ($response) {
                    $this->output->writeln(__('Deleted ' .count($pagesToDelete) . ' Pages successfully'));
                }
            }
        }

        return true;
    }

    private function getPagesToDelete($pagesToSave, $storeId)
    {
        $addedPages = $this->pagesSaver->get($storeId);
        $pagesToDelete = [];
        if ($addedPages['status'] === true && count($pagesToSave)) {
            $addedPages = $addedPages ['data'];

            $addedPages = array_column($addedPages, "id");
            $pagesToSave = array_column($pagesToSave, "id");
            $pagesToDelete = array_values(array_diff($addedPages, $pagesToSave));
        }

        return $pagesToDelete;
    }

    private function getPagesToSave($pages, $storeId)
    {
        $pagesToSave = [];
        $pagesToExclude = $this->storeAutocompleteConfig->getExcludedPages();

        foreach ($pages as $page) {
            $stores = $page->getStores();
            if (in_array($storeId, $stores) || in_array("0", $stores)) {
                $pagesToSave[] = [
                 'id' => $page->getId(),
                 'slug' => $page->getIdentifier(),
                 'content' => $page->getContent(),
                 'url' => $this->pagesManager->getUrl($page->getIdentifier()),
                 'title' => $page->getTitle(),
                 'isActive' => ($page->isActive()) ? true: false,
                 'isExcluded' => (
                     in_array($page->getId(), $pagesToExclude) ||
                     in_array(0, $pagesToExclude)
                 ) ? true : false,
                ];
            }
        }

        return $pagesToSave;
    }
}
