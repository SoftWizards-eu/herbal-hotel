<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="node_pos")
 * @ORM\Entity()
 */
class NodePos
{
    /**
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="positions")
     * @ORM\JoinColumn(name="node_id", onDelete="CASCADE")
     * @ORM\Id
     */
    private $node;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(name="target_node_id", onDelete="CASCADE")
     * @ORM\Id
     */
    private $target;

    /**
     * @ORM\Column(type="integer")
     */
    private $pos;
    /**
     * @return $node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param \w3des\AdminBundle\Entity\Node $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param \w3des\AdminBundle\Entity\Node $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return $pos
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @param mixed $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }



}

