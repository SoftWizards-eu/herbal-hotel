<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Form\Type\SliderType;
use w3des\AdminBundle\Model\AbstractNodeModule;

class SliderModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'slider';
    }

    /**
     * {@inheritDoc}
     */
    public function adminType(array $options): string
    {
        return SliderType::class;
    }
}

