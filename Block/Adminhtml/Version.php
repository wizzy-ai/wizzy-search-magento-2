<?php
namespace Wizzy\Search\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Module\ModuleListInterface;

class Version extends Template
{
    protected $moduleListInterface;

    public function __construct(
        Template\Context $context,
        ModuleListInterface $moduleListInterface,
        array $data = []
    ) {
        $this->moduleListInterface = $moduleListInterface;
        parent::__construct($context, $data);
    }

    public function getExtensionVersion()
    {
        $moduleName = "Wizzy_Search";
        $moduleInfo = $this->moduleListInterface->getOne($moduleName);
        return $moduleInfo['setup_version'];
    }
}
