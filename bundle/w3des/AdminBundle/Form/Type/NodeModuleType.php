<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\CMS;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;

class NodeModuleType extends AbstractType
{

    protected $value;

    protected $nodes;

    public function __construct(Values $value, Nodes $nodes, CMS $cms)
    {
        $this->value = $value;
        $this->nodes = $nodes;
        $this->cms = $cms;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'locales' => [],
            'config' => [],
            'allow_remove' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', HiddenType::class, array(
            'data' => $options['type']
        ));

        $builder->setAttribute('type', $options['type']);
    }

    public function getParent()
    {
        return FullNodeType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $view->vars['type'] = $form->getConfig()->getAttribute('type');
        $view->vars['allow_remove'] = $options['allow_remove'];
    }
}

