<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('dir', 'history');
        $resolver->setDefault('gallery_type', 'history');
    }

    public function getParent()
    {
        return GalleryModuleType::class;
    }
}

