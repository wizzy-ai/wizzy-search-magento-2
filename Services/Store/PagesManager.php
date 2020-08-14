<?php

namespace Wizzy\Search\Services\Store;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Url as UrlFramework;

class PagesManager {

   private $pageRepository;
   private $searchCriteriaInterface;

   private $filterBuilder;
   private $filterGroup;

   private $url;

   public function __construct(PageRepositoryInterface $pageRepository, UrlFramework $url, SearchCriteriaInterface $searchCriteriaInterface, FilterGroup $filterGroup, FilterBuilder $filterBuilder) {
      $this->pageRepository = $pageRepository;
      $this->searchCriteriaInterface = $searchCriteriaInterface;

      $this->filterGroup = $filterGroup;
      $this->filterBuilder = $filterBuilder;

      $this->url = $url;
   }

   public function fetchAll() {
      $pages = [];
      foreach($this->pageRepository->getList($this->getSearchCriteria())->getItems() as $page) {
         $pages[] = $page;
      }
      return $pages;
   }

   public function getUrl($slug) {
      return $this->url->getDirectUrl($slug);
   }

   private function getSearchCriteria() {

      $this->filterGroup->setFilters([
         $this->filterBuilder
            ->setField('is_active')
            ->setConditionType('=')
            ->setValue(1)
            ->create(),
      ]);

      return $this->searchCriteriaInterface->setFilterGroups([$this->filterGroup]);
   }
}