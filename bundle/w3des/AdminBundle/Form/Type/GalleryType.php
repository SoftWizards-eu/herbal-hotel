<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;
use Doctrine\ORM\EntityManagerInterface;

class GalleryType extends AbstractType
{

    private $nodes;

    private $values;

    private $em;

    public function __construct(EntityManagerInterface $em, Nodes $nodes, Values $values)
    {
        $this->nodes = $nodes;
        $this->values = $values;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'dir' => 'images',
            'inherit_data' => false,
            'thmb' => true
        ])->setRequired('gallery_type');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('dir', $options['dir']);

        $prototype = $builder->create('__name__', GalleryItemType::class, [
            'label' => false,
            'type' => $options['gallery_type'],
            'thmb' => $options['thmb']
        ]);
        $builder->setAttribute('prototype', $prototype);

        $builder->setAttribute('gallery_type', $options['gallery_type']);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $ev) use ($options) {
            foreach ($ev->getForm() as $tmp => $d) {
                    $ev->getForm()
                        ->remove($tmp);
            }
            if ($ev->getData()) {
                foreach ($ev->getData() as $tmp => $data) {
                    $ev->getForm()
                        ->add($tmp, GalleryItemType::class, [
                        'label' => false,
                        'type' => $ev->getForm()
                            ->getConfig()
                            ->getAttribute('gallery_type'),
                        'thmb' => $options['thmb']
                    ]);
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $ev) use ($options) {
            if ($ev->getData()) {
                foreach ($ev->getData() as $tmp => $data) {
                    $ev->getForm()
                        ->add($tmp, GalleryItemType::class, [
                        'label' => false,
                        'type' => $ev->getForm()
                            ->getConfig()
                            ->getAttribute('gallery_type'),
                        'thmb' => $options['thmb']
                    ]);
                }
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $ev) {
            $data = $ev->getData();
            foreach ($data as $k => $v) {
                if (! isset($ev->getForm()[$k])) {
                    $this->em->remove($data[$k]);
                    unset($data[$k]);
                } else {
                    $this->em->persist($data[$k]);
                }
            }
            $ev->setData($data);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $ev) {
            foreach ($ev->getData() as $el) {
                $el->setParent($ev->getForm()->getParent()->getData());
            }
            $pos = 0;
            foreach ($ev->getForm() as $k => $v) {
                $v->getData()->setPos($pos++);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['dir'] = $form->getConfig()->getAttribute('dir');
        $prototype = $form->getConfig()->getAttribute('prototype');
        $view->vars['prototype'] = $prototype->getForm()
            ->setParent($form)
            ->createView($view);
        $view->vars['thmb'] = $options['thmb'];
        $view->vars['count'] = 0;
        foreach ($form as $k => $v) {
            $view->vars['count'] = max($view->vars['count'], (int) $k);
        }
    }
}

