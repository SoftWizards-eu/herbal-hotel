<?php
namespace w3des\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SortableCollectionTypeExtension extends AbstractTypeExtension
{

    public static function getExtendedTypes(): iterable
    {
        return [
            CollectionType::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefault('sortable', null);
    }
    /**
     * {@inheritDoc}
     */
    public function finishView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $view->vars['sortable'] = $options['sortable'];
    }

}

