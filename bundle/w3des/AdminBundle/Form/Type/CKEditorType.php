<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;

class CKEditorType extends AbstractType
{

    private $contentsCss;

    private RouterInterface $router;

    private EntrypointLookupInterface $lookup;

    private TranslatorInterface $translator;

    public function __construct(EntrypointLookupInterface $lookup, TranslatorInterface $translator, RouterInterface $router)
    {
        $this->lookup = $lookup;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function getParent()
    {
        return TextareaType::class;
    }

    /**
     * @return $contentsCss
     */
    public function getContentsCss()
    {
        if ($this->contentsCss == null) {
            //$this->contentsCss = $this->lookup->getCssFiles('content');
        }
        return $this->contentsCss;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('rich', false);
        $resolver->setDefault('contentClass', 'text-content');
        $resolver->setDefault('config', []);
    }

    public function getBlockPrefix()
    {
        return 'ckeditor';
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options)
    {
        $view->vars['contentClass'] = 'ckeditor ' . $options['contentClass'];
        $view->vars['config'] = [
			  'allowedContent'  => [
				  'script'=>true,
				   '$1'=> [
          //  'elements' => 'CKEDITOR.dtd',
            'attributes' => true,
            'styles' => true,
            'classes' => true
        ]
				  ],
				'versionCheck' => false,
            'bodyClass' => $this->getContentsCss(),
            'language' => $this->getLocale(),
            'extraPlugins' => 'youtube',
            'youtube_responsive' => true,
            'filebrowserBrowseUrl' => $this->router->generate('elfinder', ['instance' => 'default']),
        ];
    }

    private function getLocale()
    {
        return $this->translator->getLocale();
    }
}

