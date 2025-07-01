<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType as BasicFileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use w3des\AdminBundle\Entity\File;
use Symfony\Component\Form\CallbackTransformer;
use w3des\AdminBundle\Service\Values;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdvFileType extends AbstractType
{

    private $values;

    private $em;

    public function __construct(Values $values, EntityManagerInterface $em)
    {
        $this->values = $values;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(function (File $file = null) {
            $model = [
                'remove' => false,
                'path' => null,
                'meta' => null,
                'width' => null,
                'height' => null,
                'mime' => null,
                'file' => null,
                'previous' => $file
           ];
            if ($file) {
                $model['path'] = $file->getPath();
                $model['meta'] = $file->getMeta();
                $model['mime'] = $file->getMime();
                $model['width'] = $file->getWidth();
                $model['height'] = $file->getHeight();
            }
            return $model;
        }, function (array $data = null) {
            if ($data['file'] instanceof UploadedFile || $data['remove']) {
                if ($data['previous'] && $data['previous']->getPath()) {
                    // XXX remove without em access, only if not used anymore
                    $this->em->remove($data['previous']);
                    $data['previous'] = null;
                }
            }
            if ($data['file'] instanceof UploadedFile) {
                return $this->values->saveFile($data['file'], null);
            }


            return $data['previous'];
        }));
        $builder->add('file', BasicFileType::class, [
            'label' => 'Wgraj nowy',
            'required' => false
        ]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($builder, $options) {
            if (empty($event->getData()) || empty($event->getData()
                ->getPath())) {
                if ($options['allow_remove']) {
                    $event->getForm()
                        ->remove('remove');
                }
            } else {
                $event->getForm()
                    ->remove('file');
                $event->getForm()
                    ->add('file', BasicFileType::class, [
                    'label' => 'Zastąp',
                    'required' => false
                ]);
                if ($options['allow_remove']) {
                    $event->getForm()
                        ->add('remove', CheckboxType::class, [
                        'label' => 'Usuń plik',
                        'required' => false
                    ]);
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'dir' => 'settings',
            'allow_remove' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'adv_file';
    }
}

