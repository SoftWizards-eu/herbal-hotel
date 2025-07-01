<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Service\CMS;
use w3des\AdminBundle\Service\ModuleRegistry;
use w3des\AdminBundle\Service\Values;
use Doctrine\ORM\EntityManagerInterface;

class NodeModulesType extends AbstractType
{

    private ModuleRegistry $registry;

    private CMS $cms;

    private TranslatorInterface $translator;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, CMS $cms, ModuleRegistry $registry, TranslatorInterface $translator)
    {
        $this->registry = $registry;
        $this->cms = $cms;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mods = [];
        foreach ($options['modules'] as $m) {
            $mod = $this->registry->getModule($m['type']);
            $formType = $mod->adminType($m['options']);
            $prototypeOptions = [
                'type' => $m['name'],
                'label' => $this->translator->trans($m['name'], [], 'admin'),
                'configure_value' => $this->fieldConfigurator($mod, $m['options']),
                'config' => $m['options']
            ];
            $mod->configureAdminField($formType, $prototypeOptions, $m['options']);
            $prototype = $builder->create('_prototype_', $formType, $prototypeOptions);
            $builder->setAttribute('prototype_' . $m['name'], $prototype);

            $mods[$m['name']] = [
                'label' => $this->translator->trans($m['name'], [], 'admin'),
                'type' => $m['type'],
                'name' => $m['name']
            ];
        }
        $builder->setAttribute('modules', $mods);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function ($event) use ($options) {
            $this->preSetData($event, $options);
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function ($event) use ($options) {
            $this->preSubmit($event, $options);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function ($ev) use ($options) {
            $data = $ev->getData();
            foreach ($data as $k => $v) {
                if (! isset($ev->getForm()[$k])) {
                    foreach ($data[$k]->getChildren() as $child) {
                        $this->em->remove($child);
                    }
                    $this->em->remove($data[$k]);
                    unset($data[$k]);
                } else {
                    $this->em->persist($data[$k]);
                }
            }
            $tmp = [];
            foreach($ev->getForm() as $k => $v) {
                $tmp[$k] = $data[$k];
            }

            $ev->setData($tmp);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['modules'] = $form->getConfig()->getAttribute('modules');
        $view->vars['prototype'] = [];
        foreach ($options['modules'] as $m) {
            $prototype = $form->getConfig()->getAttribute('prototype_' . $m['name']);
            $view->vars['prototype'][$m['name']] = $prototype->getForm()
                ->setParent($form)
                ->createView($view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getConfig()->hasAttribute('prototype') && $view->vars['prototype']->vars['multipart']) {
            $view->vars['multipart'] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('modules');
        $resolver->setDefault('label', false);
    }

    public function preSetData(PreSetDataEvent $event, array $options)
    {

        /** @var \w3des\AdminBundle\Entity\Node $module */
        foreach ($event->getForm()->all() as $k => $v) {
            $event->getForm()->remove($k);
        }
        $data = $event->getData();

        if ($data === null) {
            $data = [];
        }
        $par = $event->getForm()
            ->getParent()
            ->getParent()
            ->getParent();
        if (count($data) == 0 && $par->getData() == null || $par->getData()->getId() === null) {
            $data = [];
            $pos = 0;
            foreach ($options['modules'] as $name => $modOptions) {

                if ($modOptions['default']) {
                    $freshNode = new Node();
                    $freshNode->setLocale(Values::MODEL_DEFAULT_LOCALE);
                    $freshNode->setType($name);
                    $freshNode->setService($this->cms->getService());
                    $freshNode->setPos(0);
                    $data[] = $freshNode;
                }
            }
            $event->setData($data);
        }
        $pos = 0;
        foreach ($data as $module) {
            $mod = $this->registry->getModule($module->getType());
            $opts = $options['modules'][$module->getType()]['options'];
            $formType = $mod->adminType($options);
            $prototypeOptions = [
                'label' => $module->getType(),
                'type' => $module->getType(),
                'config' => $opts,
                'configure_value' => $this->fieldConfigurator($mod, $opts)
            ];

            $mod->configureAdminField($formType, $prototypeOptions, $opts);
            $event->getForm()->add($pos . '', $formType, $prototypeOptions);
            $pos ++;
        }
    }

    public function preSubmit(PreSubmitEvent $event, array $options)
    {
        foreach ($event->getForm() as $k => $v) {
            $event->getForm()->remove($k);
        }
        if (\is_array($event->getData())) {
            foreach ($event->getData() as $pos => $data) {
                $mod = $this->registry->getModule($data['type']);
                $opts = $options['modules'][$data['type']]['options'];
                $formType = $mod->adminType($opts);

                $prototypeOptions = [
                    'label' => $data['type'],
                    'type' => $data['type'],
                    'config' => $opts,
                    'configure_value' => $this->fieldConfigurator($mod, $opts)
                ];
                $mod->configureAdminField($formType, $prototypeOptions, $opts);
                $event->getForm()->add($pos . '', $formType, $prototypeOptions);
            }
        }
    }

    public function fieldConfigurator(NodeModuleInterface $mod, array $opts)
    {
        return function ($name, &$fieldOptions) use ($mod, $opts) {
            $mod->configureAdminField($name, $fieldOptions, $opts);
        };
    }
}

