<?php
namespace w3des\AdminBundle\Form\Type;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeUrl;
use w3des\AdminBundle\Entity\NodeVariable;
use w3des\AdminBundle\Service\CMS;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;

class FullNodeType extends AbstractType
{

    private Nodes $nodes;

    private EntityManagerInterface $em;

    private CMS $cms;

    public function __construct(Nodes $node, EntityManagerInterface $em, CMS $cms)
    {
        $this->nodes = $node;
        $this->em = $em;
        $this->cms = $cms;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cfg = $this->nodes->getNodeCfg($options['type']);
        if ($cfg['embed']['enabled']) {
            if ($options['embed']) {
                $cfg['fields'][$cfg['embed']['field']]['options']['type'] = $options['embed'];
            } else {
                $item = $this->nodes->getVariables($options['data'])->get($cfg['embed']['field']);
                $cfg['fields'][$cfg['embed']['field']]['options']['type'] = $options['embed'] = $item->getType();
            }
        }

        $builder->setAttribute('type', $options['type']);
        $builder->setAttribute('definition', $cfg);

        if ($options['sections'] && count($cfg['sections']) > 1) {
            $sections = $cfg['sections'];
        } else {
            $sections = false;
        }
        $builder->add('variables', ValuesType::class, [
            'fields' => $cfg['fields'],
            'label' => false,
            'value_type' => function () use ($options) {
                $tmp = new NodeVariable();
                return $tmp;
            },
            'sections' => $sections,
            'configure_value' => $options['configure_value'],
            'label_prefix' => 'node.field.',
            'locales' => $options['locales']
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [
            $this,
            'preSetData'
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [
            $this,
            'preSubmit'
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function ($event) use ($options) {
            $this->submit($event, $options);
        }, 50);
    }

    public function preSetData(PreSetDataEvent $ev)
    {
        if ($ev->getData() == null) {
            $ev->setData($ev->getForm()->getConfig()->getEmptyData()($ev->getForm()));
        }
    }

    public function submit(PostSubmitEvent $ev, array $options)
    {
        $node = $ev->getForm()->getData();
        foreach ($node->getVariables() as $v) {
            $v->setNode($node);
        }
    }

    public function getParent()
    {
        return FormType::class;
    }

    public function preSubmit(PreSubmitEvent $event)
    {
        /** @var \w3des\AdminBundle\Entity\Node $node */
        $node = $event->getForm()->getData();
        $type = $event->getForm()
            ->getConfig()
            ->getAttribute('type');
        $cfg = $this->nodes->getNodeCfg($type);
        if (! $cfg['url']) {
            return;
        }

        $data = $event->getData();
        foreach ($data['variables']['url'] as $locale => $newUrl) {
            $orgLocale = $locale;
            if ($locale == Values::MODEL_DEFAULT_LOCALE && $cfg['locale']) {
                $locale = $node->getLocale();
            }
            $url = $node->getUrl($locale);

            if ($newUrl == '') {
                $vars = $data['variables'][$cfg['url']];
                if (isset($vars[$locale])) {
                    $newUrl = $vars[$locale];
                } else if (isset($vars[Values::MODEL_DEFAULT_LOCALE]))  {
                    $newUrl = $vars[Values::MODEL_DEFAULT_LOCALE];
                }
            }
            $sl = new Slugify();
            $newUrl = $sl->slugify($newUrl);

            if ($url == null) {
                $url = new NodeUrl();
                $url->setNode($node);
                $url->setLocale($locale);
                $url->setService($node->getService());

                $node->setUrl($url);
            }

            if ($newUrl == '') {
                $newUrl = 'node';
            }
            if ($newUrl != $url->getSlug()) {
                $this->findUrl($newUrl, $url, $locale);
                $data['variables']['url'][$orgLocale] = $url->getSlug();
                $event->setData($data);
            }
        }

    }

    private function findUrl($newUrl, NodeUrl $url)
    {
        $count = 0;
        $q = $this->em->createQuery('select u from w3desAdminBundle:NodeUrl u where u.service =:service and u.locale = :locale and u.slug = :slug and u.id != :id')->setParameters([
            'slug' => $newUrl . ($count ? '-' . $count : ''),
            'locale' => $url->getLocale(),
            'service' => $url->getService(),
            'id' => (int) $url->getId()
        ]);
        while (count($q->execute())) {
            $count ++;
            $q->setParameter('slug', $newUrl . ($count ? '-' . $count : ''));
        }
        $newUrl = $newUrl . ($count ? '-' . $count : '');
        $url->setSlug($newUrl);
        $url->setPath($newUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Node::class);
        $resolver->setDefault('sections', false);
        $resolver->setDefault('page_locale', false);
        $resolver->setDefault('configure_value', null);
        $resolver->setDefault('empty_data', function (Options $options) {
            return function (FormInterface $form) use ($options) {
                $data = new Node();
                $data->setService($this->cms->getService());
                $data->setType($options['type']);
                $data->setPos(0);
                $data->setLocale($options['page_locale'] ? $this->cms->getLocale() : Values::MODEL_DEFAULT_LOCALE);
                return $data;
            };
        });
        $resolver->setDefault('embed', null);
        $resolver->setDefault('locales', [$this->cms->getLocale()]);
        $resolver->setRequired('type')
            ->setNormalizer('type', function (Options $options, $value) {
            if (! \is_string($value)) {
                throw new \InvalidArgumentException('Type have to be string');
            }
            if (empty($this->nodes->getNodeCfg($value))) {
                throw new \InvalidArgumentException('Type not exists');
            }

            return $value;
        });
    }

    public function getBlockPrefix()
    {
        return 'cms_node';
    }
}

