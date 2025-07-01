<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use w3des\AdminBundle\Model\ValueInterface;
use w3des\AdminBundle\Model\ValueTrait;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="w3des\AdminBundle\Repository\SettingsRepository")
 */
class Setting implements ValueInterface
{
    use ValueTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $service;

    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2)
     * @ORM\Id
     */
    private $locale;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $pos = 0;
    /**
     * @return $service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }


}

