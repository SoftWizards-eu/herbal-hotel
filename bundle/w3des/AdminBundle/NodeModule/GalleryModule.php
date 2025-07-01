<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use w3des\AdminBundle\Form\Type\GalleryModuleType;
use w3des\AdminBundle\Model\AbstractNodeModule;

class GalleryModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'gallery';
    }

    public static function fields(): array
    {
        return [
            'type' => [
                'storeType' => 'integer',
                'default' => 1,
                'type' => HiddenType::class
            ],
            'thumb' => [
                'storeType' => 'string',
                'default' => 'node_gallery_outbound',
                'type' => ChoiceType::class,
                'options' => [
                    'empty_data' => 1,
                    'label_format' => 'node.field.thumb',
                    'choices' => [
                        'Wypełnienie' => 'node_gallery_outbound',
                        'Wyśrodkowany' => 'node_gallery_inset',
                        //'Designerska' => 'node_gallery_design'
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function adminType(array $options): string
    {
        return GalleryModuleType::class;
    }
}

