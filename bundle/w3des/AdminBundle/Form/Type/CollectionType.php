<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as BaseCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Model\ValueTypeInterface;
use w3des\AdminBundle\Util\ValueTypeDecoder;

class CollectionType extends AbstractType implements ValueTypeInterface
{

    public function getStoreType(array $options): string
    {
        return ValueTypeDecoder::decode($options['entry_type'], $options);
    }

    public function getParent()
    {
        return BaseCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'sortable' => true,
            'entry_options' => [
                'label' => false
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['sortable'] = $options['sortable'];
    }
}

