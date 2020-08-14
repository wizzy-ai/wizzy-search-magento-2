<?php
declare(strict_types=1);

namespace Wizzy\Search\Model\Admin\Source\SelectBlocks;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Wizzy\Search\Services\Store\PagesManager;

class PagesList extends Select {

   private $pagesManager;

   public function __construct(Context $context, PagesManager $pagesManager, array $data = []) {
      parent::__construct($context, $data);
      $this->pagesManager = $pagesManager;
   }

   /**
    * Set "name" for <select> element
    *
    * @param string $value
    * @return $this
    */
   public function setInputName($value) {
      return $this->setName($value);
   }

   /**
    * Set "id" for <select> element
    *
    * @param $value
    * @return $this
    */
   public function setInputId($value) {
      return $this->setId($value);
   }

   /**
    * Render block HTML
    *
    * @return string
    */
   public function _toHtml(): string {
      if (!$this->getOptions()) {
         $this->setOptions($this->getSourceOptions());
      }
      return parent::_toHtml();
   }

   private function getSourceOptions(): array {
      $pages = $this->pagesManager->fetchAll();
      $options = [];
      $options[] = [
        'value' => '0',
        'label' => 'All Pages',
      ];

      foreach ($pages as $page) {
         $options[] = [
            'value' => $page->getId(),
            'label' => $page->getTitle(),
         ];
      }

      return $options;
   }
}