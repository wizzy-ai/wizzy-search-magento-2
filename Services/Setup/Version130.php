<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version130
{
    private $setupUtils;
    private $setup;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
      'wizzy_general_configuration/general_configuration/enable_analytics' => '1',
      'wizzy_advanced_configuration/advanced_configuration/overriding_eventsjs' => '0',
    ];

    public function update(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->setDefaults();
    }

    public function install(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->setDefaults();
    }

    private function setDefaults()
    {
        $this->setDefaultConfig();
    }

    private function setDefaultConfig()
    {
        $this->setupUtils->setDefaultConfig($this->defaultConfigs);
    }
}
