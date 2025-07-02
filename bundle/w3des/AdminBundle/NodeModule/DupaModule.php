<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use w3des\AdminBundle\Form\Type\CKEditorType;
use w3des\AdminBundle\Model\AbstractNodeModule;

class DupaModule extends AbstractNodeModule
{
    public static function name(): string
    {
        return 'dupa_form';
    }

    public static function fields(): array
    {
        return [
            'title' => [
                'label' => 'Nagłówek',
                'index' => true,
                'type' => TextType::class,
                'options' => []
            ],
            'subtitle' => [
                'label' => 'Podtytuł',
                'index' => false,
                'type' => TextType::class,
                'options' => []
            ],
            'content' => [
                'label' => 'Treść',
                'index' => false,
                'type' => CKEditorType::class,
                'options' => []
            ]
        ];
    }
}