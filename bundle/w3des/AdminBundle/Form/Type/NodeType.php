<?php
namespace w3des\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\CMS;
use w3des\AdminBundle\Service\Nodes;

class NodeType extends AbstractType
{

    private $em;

    private $nodes;

    private $cms;

    private $translator;

    public function __construct(EntityManagerInterface $em, Nodes $nodes, CMS $cms)
    {
        $this->em = $em;
        $this->nodes = $nodes;
        $this->cms = $cms;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {}

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('type');
        $resolver->setDefault('load', true);
        $resolver->setDefault('choice_loader', function (Options $options) {
            $cfg = $this->nodes->getNodeCfg($options['type']);
            return new CallbackChoiceLoader(function () use ($options, $cfg) {
                $raw = $this->nodes->getNodes($options['type'], $cfg['sortable'] ? [
                    'parent' => null
                ] : [
                    'orderBy' => [$cfg['title'] => 'asc']
                ]);
                return $this->flat($raw['list'], $cfg['sortable']);
            });
        });
        $resolver->setDefault('choice_label', function (Options $options) {
            return function ($node) use ($options) {
                $prefix = '';
                $p = $node->getParent();
                while ($p) {
                    $prefix .= '--';
                    $p = $p->getParent();
                }
                return ($prefix ? $prefix . '| ' : '') . $this->nodes->getVariable($node, $this->nodes->getNodeCfg($node->getType())['title'], $this->cms->getLocale());
            };
        });
            /*$resolver->setDefault('choice_value', function ($v) {
                if ($v === null) {
                    return null;
                }
                return $v->getId();
            });*/
    }

    private function flat($raw, $child, $lvl = 0)
    {
        $list = [];
        foreach ($raw as $item) {
            $list[] = $item->model;
            if ($child) {
                $list = \array_merge($list, $this->flat($item->children, $child, $lvl+1));
            }
        }

        return $list;
    }
}

