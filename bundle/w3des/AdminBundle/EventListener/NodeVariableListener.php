<?php
namespace w3des\AdminBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use w3des\AdminBundle\Entity\File;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeVariable;
use w3des\AdminBundle\Entity\Setting;
use w3des\AdminBundle\Model\ValueInterface;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;

class NodeVariableListener
{

    private Values $values;

    private Nodes $nodes;

    private array $recalc = [];
    private array $files = [];

    public function __construct(Values $values, Nodes $nodes)
    {
        $this->values = $values;
        $this->nodes = $nodes;
    }

    private function preRemove(ValueInterface $value, EntityManager $em): void
    {
        if ($value->getType() == 'node') {
            $node = $value->getValue();
            if ($node == null || $node->getId() == null) {
                return;
            }
            if ($this->nodes->getNodeCfg($node->getType())['autoClean']) {
                if (! $this->nodes->inUse($node, $value)) {
                    $em->remove($node);
                    $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(Node::class), $node);
                }
            }
        } elseif ($value->getType() == 'file') {
            /** @var \w3des\AdminBundle\Entity\File $file */
            $file = $value->getFileValue();
            if ($file != null) {
                if (!isset($this->files[$file->getId()])) {
                    $this->files[$file->getId()] = 0;
                }
                $this->files[$file->getId()]++;
            }
        }
    }

    private function prePersist(ValueInterface $value, EntityManager $em)
    {
        if ($value->getType() == 'node') {
            $em->persist($value->getValue());
            $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(Node::class), $value->getValue());
        }
    }


    public function preFlush(PreFlushEventArgs $args)
    {
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $remove) {
            if ($remove instanceof ValueInterface) {
                $this->preRemove($remove, $args->getEntityManager());
            }
        }
        $this->checkFiles($args->getEntityManager());
    }

    private function checkFiles(EntityManagerInterface $em)
    {
        foreach ($this->files as $id => $cnt) {
            if ($em->getRepository(Setting::class)->fileInUse($id) + $em->getRepository(Node::class)->fileInUse($id) <= $cnt) {
                $node = $em->getReference(File::class, $id);
                $em->remove($node);
                $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(File::class), $node);
            }
        }
        $this->files = [];
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $map = $args->getEntityManager()->getUnitOfWork()->getIdentityMap();
        if (isset($map[NodeVariable::class]) && count($map[NodeVariable::class])) {
            $retry = [];
            $qb = $args->getEntityManager()->createQueryBuilder()->update(NodeVariable::class, 'v');
            $q = $qb->set('v.pos', ':new_pos')->where('v.node = :node and v.pos = :pos and v.locale = :locale and v.name = :name')->getQuery();

            /** @var \w3des\AdminBundle\Entity\NodeVariable $var */
            foreach ($map[NodeVariable::class] as $var) {
                if ($var->getNewPos() !== null && $var->getNewPos() != $var->getPos()) {
                    $retry[] = $var;
                  //  var_dump($var->getNewPos(), $var->getPos());

                }
            }
            foreach ($retry as $var) {
                $q->execute([
                    'new_pos' => $var->getPos() * -1 - 1,
                    'pos' => $var->getPos(),
                    'locale' => $var->getLocale(),
                    'name' => $var->getName(),
                    'node' => $var->getNode()
                ]);

            }
            foreach ($retry as $var) {
                $q->execute([
                    'new_pos' => $var->getNewPos(),
                    'pos' => $var->getPos() * -1 - 1,
                    'locale' => $var->getLocale(),
                    'name' => $var->getName(),
                    'node' => $var->getNode()
                ]);
                $var->setPos($var->getNewPos());
                $var->setNewPos(null);
            }
        }
    }
}

