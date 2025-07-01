<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;

class GalleryItemType extends AbstractType
{

    private $values;

    private $nodes;

    private $stack;

    public function __construct(Nodes $nodes, Values $values, RequestStack $stack)
    {
        $this->values = $values;
        $this->nodes = $nodes;
        $this->stack = $stack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['thmb'] = $options['thmb'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('gallery_type', $options['type']);
        $builder->add('pos', HiddenType::class, [
            'attr' => [
                'pos' => 'gallery-pos'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('thmb', true);
    }

    public function getParent()
    {
        return FullNodeType::class;
    }
}

