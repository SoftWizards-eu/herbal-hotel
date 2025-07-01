<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Entity\Node;

class ModuleInfo
{
    private NodeView $instance;

    private int $pos;

    private NodeView $parent;

    private string $section;

    public function __construct(NodeView $instance, int $pos, NodeView $parent, string $section)
    {
        $this->instance = $instance;
        $this->pos = $pos;
        $this->parent = $parent;
        $this->section = $section;
    }
    /**
     * @return $instance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param \w3des\AdminBundle\Entity\Node $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return $pos
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @param int $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * @return $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \w3des\AdminBundle\Entity\Node $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return $section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }


}

