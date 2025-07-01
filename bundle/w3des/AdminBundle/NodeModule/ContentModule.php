<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Form\Type\CKEditorType;
use w3des\AdminBundle\Model\AbstractNodeModule;

class ContentModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'content';
    }

    public static function fields(): array
    {
        return [
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

