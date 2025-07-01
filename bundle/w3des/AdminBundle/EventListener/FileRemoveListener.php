<?php
namespace w3des\AdminBundle\EventListener;

use w3des\AdminBundle\Service\Values;
use Doctrine\ORM\Event\LifecycleEventArgs;
use w3des\AdminBundle\Entity\File;

class FileRemoveListener
{

    private $values;

    public function __construct(Values $values)
    {
        $this->values = $values;
    }

    public function prePersist(File $value, LifecycleEventArgs $event): void
    {}

    public function preUpdate(File $value, LifecycleEventArgs $event): void
    {}

    public function postRemove(File $value, LifecycleEventArgs $event): void
    {
        $this->values->removeFile($value->getPath());
    }
}

