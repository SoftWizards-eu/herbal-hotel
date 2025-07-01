<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\Values;

class UploadedImageType extends AbstractType
{

    private Values $values;

    public function __construct(Values $values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('new', HiddenType::class, []);
        $builder->addModelTransformer(new CallbackTransformer(function ($data) {
            if ($data === null) {
                return [
                    'new' => '__path__',
                    'path' => null,
                    'model' => null
                ];
            }
            return [
                'model' => $data,
                'path' => $data->getPath(),
                'new ' => null
            ];
        }, function ($v) {
            if ($v['new']) {
                return $this->values->fileFromPath($v['new']);
            } else {
                return $v['model'];
            }
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('dir', 'image')->setDefault('label', false);
    }
}

