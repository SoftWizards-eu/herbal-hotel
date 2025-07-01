<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use w3des\AdminBundle\Model\ValuesMap;

/**
 * @ORM\Table(indexes={
 *  @ORM\Index(name="node_type_idx", columns={"service", "type", "locale"}),
 *  @ORM\Index(name="node_external_id", columns={"external_id"})
 * })
 * @ORM\Entity(repositoryClass="w3des\AdminBundle\Repository\NodeRepository")
 */
class Node
{

    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $service;

    /**
     * @ORM\Column(type="integer")
     */
    private $pos;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\NodeVariable", mappedBy="node", orphanRemoval=true, fetch="EAGER", cascade={"all"})
     * @ORM\OrderBy({"pos" = "asc"})
     */
    private $variables;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\Node", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\Node", mappedBy="parent")
     * @ORM\OrderBy({"pos" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\NodeUrl", indexBy="locale", mappedBy="node",  fetch="EAGER", cascade={"all"})
     */
    private $urls;

    /**
     * @var ValuesMap
     */
    private $variableMap;

    /**
     * @var string
     * @ORM\Column(name="external_id", nullable=true)
     */
    private $externalId;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $meta;

    /**
     * @ORM\OneToMany(targetEntity="NodePos", mappedBy="node", cascade={"all"})
     */
    private $positions;

    public function __construct()
    {
        $this->variables = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->positions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getPos()
    {
        return $this->pos;
    }

    public function setPos($pos)
    {
        $this->pos = (int)$pos;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function setVariables($variable)
    {
        $this->variables = $variable;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    public function getUrl($locale = null)
    {
        if ($locale) {
            return $this->urls[$locale];
        }
        if ($this->urls->containsKey($this->locale)) {
            return $this->urls[$this->locale];
        }
        return $this->urls->first();
    }

    public function setUrl($url)
    {
        $this->urls[$url->getLocale()] = $url;
        $url->setNode($this);
        return $this;
    }

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
    /**
     * @return $variableMap
     */
    public function getVariableMap()
    {
        return $this->variableMap;
    }

    /**
     * @param mixed $variableMap
     */
    public function setVariableMap($variableMap)
    {
        $this->variableMap = $variableMap;
    }

    public function __toString()
    {
        return sprintf('%s %s:%s', $this->locale, $this->type, $this->id);
    }
    /**
     * @return $externalId
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }

    /**
     * @return $meta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }
    /**
     * @return $positions
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @param Ambigous <\Doctrine\Common\Collections\Collection, multitype:\w3des\AdminBundle\Entity\NodePos > $positions
     */
    public function setPositions($positions)
    {
        $this->positions = $positions;
    }
}

