<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Form\Type\GalleryModuleType;
use w3des\AdminBundle\Model\AbstractNodeModule;

class FileModule extends AbstractNodeModule
{

    public static function name(): string
    {
        return 'file';
    }

    /**
     * {@inheritDoc}
     */
    public function adminType(array $options): string
    {
        return GalleryModuleType::class;
    }

    public function configureAdminField(string $name, array &$fieldOptions, array $options): void
    {
        if ($name === GalleryModuleType::class) {
            $fieldOptions['gallery_type'] = 'file';
            $fieldOptions['thmb'] = false;
        }
    }
}

