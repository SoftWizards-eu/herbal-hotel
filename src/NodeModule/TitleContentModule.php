<?php
namespace App\NodeModule;

use w3des\AdminBundle\Form\Type\CKEditorType;
use w3des\AdminBundle\Model\AbstractNodeModule;
use App\Form\IconType;

class TitleContentModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'title_content';
    }

    public static function fields(): array
    {
        return [
            'title' => [
                'label' => 'Tytuł'
            ],
            'icon' => [
                'type' => IconType::class,
                'storeType' => 'string'
            ],
            'content' => [
                'label' => 'Treść',
                'type' => CKEditorType::class,
                'index' => true,
                'options' => [
                    'config' => [
                        'bodyClass' => 'text text-content'
                    ]
                ]
            ]
        ];
    }
}

