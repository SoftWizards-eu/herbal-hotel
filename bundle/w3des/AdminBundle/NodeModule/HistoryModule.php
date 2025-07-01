<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use w3des\AdminBundle\Model\AbstractNodeModule;
use w3des\AdminBundle\Form\Type\CKEditorType;

class HistoryModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'history';
    }


    public static function fields(): array
    {
        return [
            'year' => [
                'label' => 'Rok',
                'type' => TextType::class,
                'index' => true
            ],
            'content' => [
                'label' => 'Treść',
                'type' => CKEditorType::class,
                'index' => true,
                'options' => [
                    'config' => [
                        'bodyClass' => 'text text-content year-content'
                    ]
                ]
            ]
        ];
    }
}

