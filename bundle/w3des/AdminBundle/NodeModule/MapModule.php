<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use w3des\AdminBundle\Model\AbstractNodeModule;

class MapModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'map';
    }

    public static function fields(): array
    {
        return [
            'title' => [
                'label' => 'Tytuł'
            ],
            'content' => [
                'label' => 'Treść w dymku',
                'type' => TextareaType::class
            ],
            'lat' => [
                'label' => 'Szerokość'
            ],
            'lng' => [
                'label' => 'Długość'
            ]
        ];
    }
}

