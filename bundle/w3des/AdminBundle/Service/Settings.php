<?php
namespace w3des\AdminBundle\Service;

use w3des\AdminBundle\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use w3des\AdminBundle\Model\ValuesMap;
use Doctrine\Common\Collections\ArrayCollection;

class Settings
{
    private $em;

    private $cms;

    private $trans;

    private $loaded = null;

    private $values;

    private $sections;

    /**
     * @var array
     */
    private $fields = [];

    public function __construct(CMS $cms, TranslatorInterface $trans, EntityManagerInterface $entityManager, Values $values, $sections, $fields)
    {
        $this->em = $entityManager;
        $this->values = $values;
        $this->sections = $sections;
        $this->fields = $fields;
        $this->trans = $trans;
        $this->cms = $cms;
    }

    private function load($locale)
    {
        if (isset($this->loaded[$locale])) {
            return;
        }
        $raw = $this->em->getRepository(Setting::class)->findBy([
            'locale' => [Values::MODEL_DEFAULT_LOCALE,$locale],
            'service' => $this->cms->getService()
        ]);
        ;


        $this->loaded[$locale] = $this->values->createMap(new ArrayCollection($raw), $this->fields, $locale);

    }

    public function get($name, $default = null, $locale = null)
    {

        $this->load($this->cms->getLocale());


        return $this->loaded[$this->cms->getLocale()]->get($name, $this->cms->getLocale()) ?: $default;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getField($name)
    {
        return $this->fields[$name];
    }


}

