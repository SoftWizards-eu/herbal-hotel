<?php
namespace w3des\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use w3des\AdminBundle\Util\ValueTypeDecoder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('w3des_admin');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('w3des_admin');
        }

        $this->addSettingsSection($rootNode);
        $this->addNodeSection($rootNode);
        $this->addModuleSection($rootNode);
        $this->addServicesSection($rootNode);

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }


    private function addNodeSection(ArrayNodeDefinition $root)
    {
        //@formatter:off
        $root->children()
                ->arrayNode('nodes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('sortable')->isRequired()->end()
                            ->booleanNode('autoClean')->defaultTrue()->end()
                            ->booleanNode('locale')->isRequired()->end()
                            ->scalarNode('icon')->defaultValue('page')->end()
                            ->scalarNode('url')->defaultNull()->end()
                            ->scalarNode('title')->defaultNull()->end()
                            ->scalarNode('defaultSort')->defaultValue('_pos')->end()
                            ->scalarnode('defaultSortDirection')->defaultValue('asc')->end()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->integerNode('maxDepth')->defaultValue(1)->end()
                            ->booleanNode('index')->defaultValue(false)->end()
                            ->variableNode('redirectEmpty')->defaultValue(null)->end()
                            ->arrayNode('embed')
                                ->canBeEnabled()
                                ->beforeNormalization()
                                    ->always(function($v) {
                                        if (count($v['type'])  ) {
                                            $v['enabled'] = true;
                                        }
                                        return $v;
                                    } )
                                ->end()
                                ->treatFalseLike(['enabled' => false])
                                ->treatTrueLike(['enabled' => true])
                                ->treatNullLike(false)
                                ->children()
                                    ->scalarNode('field')->defaultValue('node')->end()
                                    ->variableNode('type')->defaultValue([])->end()
                                ->end()
                            ->end()
                            ->arrayNode('sections')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('fields')
                                            ->useAttributeAsKey('name')
                                            ->prototype('array')
                                                ->beforeNormalization()->always(function($v) {
                                                    if (!isset($v['type'])) {
                                                        $v['type'] = TextType::class;
                                                    }
                                                    if (empty($v['storeType'])) {
                                                        $v['storeType'] = ValueTypeDecoder::decode($v['type'], $v['options']??[]);
                                                    }

                                                    return $v;
                                                })->end()
                                                ->children()
                                                    ->booleanNode('grid')->defaultFalse()->end()
                                                    ->variableNode('default')->defaultValue(null)->end()
                                                    ->scalarNode('type')->defaultValue(TextType::class)->end()
                                                    ->scalarNode('storeType')->isRequired()->end()
                                                    ->booleanNode('locale')->defaultFalse()->end()
                                                    ->booleanNode('array')->defaultFalse()->end()
                                                    ->booleanNode('sortable')->defaultTrue()->end()
                                                    ->booleanNode('index')->defaultFalse()->end()
                                                    ->variableNode('options')->defaultValue([])->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('modules')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('type')->defaultValue(TextType::class)->end()
                                                    ->variableNode('options')->defaultValue([])->end()
                                                    ->booleanNode('default')->defaultFalse()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                ->end()
             ->end();

        //@formatter:on
    }

    private function addModuleSection(ArrayNodeDefinition $root)
    {
        //@formatter:off
        $root->fixXmlConfig('moduleGroup')
            ->children()
                ->arrayNode('modules')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                    ->variableNode('options')->defaultValue([])->end()
                    ->end()
                ->end()
            ->end()
        ;
        //@formatter:on
    }

    private function addSettingsSection(ArrayNodeDefinition $root)
    {
        //@formatter:off
        $root->fixXmlConfig('settingGroup')
            ->children()
                ->arrayNode('settings')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('setting')
                        ->useAttributeAsKey('name')
                        ->requiresAtLeastOneElement()
                        ->prototype('array')
                            ->beforeNormalization()->always(function($v) {
                                if (isset($v['fields']) && count($v['fields'])) {
                                    $v['type'] = FormType::class;
                                    $v['storeType'] = 'ignore';
                                } else if (!isset($v['type'])) {
                                    $v['type'] = TextType::class;
                                }
                                if ($v['type'] != FormType::class && empty($v['storeType'])) {
                                    $v['storeType'] = ValueTypeDecoder::decode($v['type'], $v['options']??[]);
                                }

                                return $v;
                            })->end()
                            ->children()
                                ->scalarNode('type')->isRequired()->end()
                                ->scalarNode('storeType')->isRequired()->end()
                                ->booleanNode('locale')->defaultTrue()->end()
                                ->variableNode('default')->defaultValue(null)->end()
                                ->variableNode('options')->defaultValue([])->end()
                                ->booleanNode('array')->defaultFalse()->end()
                                ->arrayNode('fields')
                                    ->fixXmlConfig('setting')
                                    ->useAttributeAsKey('name')
                                    ->requiresAtLeastOneElement()
                                    ->prototype('array')
                                    ->beforeNormalization()->always(function($v) {
                                        if ($v == null) {
                                            $v = [];
                                        }
                                        if (empty($v['type'])) {
                                            $v['type'] = TextType::class;
                                        }

                                        if (!isset($v['storeType'])) {
                                            $v['storeType'] = ValueTypeDecoder::decode($v['type'], $v['options']??[]);
                                        }

                                        return $v;
                                    })->end()
                                    ->children()
                                        ->booleanNode('locale')->defaultTrue()->end()
                                        ->scalarNode('type')->defaultValue(TextType::class)->end()
                                        ->variableNode('options')->defaultValue([])->end()
                                        ->scalarNode('storeType')->isRequired()->end()
                                        ->variableNode('default')->defaultValue(null)->end()
                                        ->booleanNode('array')->defaultFalse()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                  ->end()
              ->end()
           ->end()
        ;
           // @formatter:on
    }

    private function addServicesSection(ArrayNodeDefinition $root)
    {
        //@formatter:off
        $root->fixXmlConfig('servicesGroup')
            ->children()
                ->arrayNode('services')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                        ->scalarNode('id')->isRequired()->end()
                        ->scalarNode('title')->isRequired()->end()
                        ->scalarNode('entry')->defaultValue('app')->end()
                        ->variableNode('extra')->defaultValue([])->end()
                        ->variableNode('domains')->defaultValue([])->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // @formatter:on
    }
}
