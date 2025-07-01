<?php
namespace w3des\AdminBundle\Service;

use Psr\Container\ContainerInterface;
use w3des\AdminBundle\Model\NodeModuleInterface;

class ModuleRegistry
{

    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    public function getModule($type): NodeModuleInterface
    {
        return $this->locator->get($type);
    }
}

