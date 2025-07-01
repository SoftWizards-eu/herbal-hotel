<?php
namespace w3des\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Service\Settings;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Form\Type\NodeModulesType;
use w3des\AdminBundle\Service\ModuleRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use w3des\AdminBundle\Service\CMS;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class w3desAdminExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $moduleLocator = [];

        $settingsSections = [];
        $settingsFields = [];
        foreach ($config['settings'] as $name => $gr) {
            $settingsSections[$name] = [];
            foreach ($gr as $k => $v) {
                if ($v['type'] == FormType::class) {
                    $list = [];
                    foreach ($v['fields'] as $sk => $sv) {
                        $list[] = $sk;
                        $field = new ValueDefinition($sk, $sv);
                        $settingsFields[$sk] = $field->toArray();
                    }
                    $settingsSections[$name][$k] = $list;
                } else {
                    $field = new ValueDefinition($k, $v);
                    $settingsSections[$name][] = $k;
                    $settingsFields[$k] = $field->toArray();
                }
            }
        }

        $container->getDefinition(Settings::class)
            ->setArgument('$sections', $settingsSections)
            ->setArgument('$fields', $settingsFields);
        $nodes = [];

        foreach ($config['nodes'] as $nodeName => $nodeCfg) {
            $cfg = $nodeCfg;
            unset($cfg['nodes']);
            $cfg['sections'] = [];
            $cfg['fields'] = [];
            $cfg['grid'] = [];
            $cfg['modules'] = [];
            foreach ($nodeCfg['sections'] as $sectionName => $sectionCfg) {
                $cfg['sections'][$sectionName] = [];
                foreach ($sectionCfg['fields'] as $name => $field) {
                    if ($field['grid']) {
                        $cfg['grid'][] = $name;
                    }
                    unset($field['grid']);
                    $field = new ValueDefinition($name, $field);
                    $cfg['sections'][$sectionName][] = $name;
                    $cfg['fields'][$name] = $field->toArray();
                }

                $sectionModules = [];
                foreach ($sectionCfg['modules'] as $module) {
                    // $cfg['sections'][$sectionName]['modules'][] = $module;
                    if (! isset($config['modules'][$module['type']])) {
                        $config['modules'][$module['type']] = [
                            'options' => []
                        ];
                    }
                    $defaultOptions = $config['modules'][$module['type']]['options'];
                    $name = \call_user_func($module['type'] . '::name');
                    if (empty($nodes['module.' . $name])) {
                        $nodes['module.' . $name] = $this->prepareModuleNode($module);
                        $moduleLocator['module.' . $name] = new Reference($module['type']);
                        $moduleLocator[$module['type']] = new Reference($module['type']);
                    }

                    $moduleOptions = array_replace([
                        'section' => $sectionName
                    ], $defaultOptions, $module['options'] ?? []);
                    $resolver = $this->basicModuleOptions();
                    \call_user_func($module['type'] . '::configureOptions', $resolver);
                    $module['options'] = $resolver->resolve($moduleOptions);
                    $module['name'] = 'module.' . $name;
                    $sectionModules['module.' . $name] = $module;
                }
                if (count($sectionModules)) {
                    $cfg['sections'][$sectionName][] = $sectionName . '_modules';
                    $cfg['fields'][$sectionName . '_modules'] = (new ValueDefinition($sectionName . '_modules', [
                        'locale' => false,
                        'storeType' => 'node',
                        'array' => true,
                        'type' => NodeModulesType::class,
                        'options' => [
                            'modules' => $sectionModules
                        ]
                    ]))->toArray();
                    $cfg['modules'][$sectionName] = [
                        'field' => $sectionName . '_modules',
                        'options' => $sectionModules
                    ];
                }
            }

            $nodes[$nodeName] = $cfg;
        }
        $container->getDefinition(Nodes::class)->setArgument('$cfg', $nodes);
        $container->getDefinition(ModuleRegistry::class)->setArgument('$locator', ServiceLocatorTagPass::register($container, $moduleLocator));
        $container->getDefinition(CMS::class)->setArgument('$services', $config['services']);

    }

    private function prepareModuleNode(array $module)
    {
        $data = [
            'sortable' => false,
            'locale' => false,
            'icon' => null,
            'enabled' => false,
            'maxDepth' => 1,
            'title' => null,
            'autoClean' => true,
            'url' => null,
            'index' => true,
            'embed' => [
                'enabled' => false
            ],
            'redirectEmpty' => null,
            'sections' => [
                'statics' => []
            ],
            'fields' => [],
            'grid' => []
        ];
        foreach (\call_user_func($module['type'] . '::fields') as $name => $field) {
            $field = new ValueDefinition($name, $field);
            $data['sections']['statics'][] = $field->name;
            $data['fields'][$field->name] = $field->toArray();
        }

        return $data;
    }

    private function fixField($name, $data)
    {
        $s = new ValueDefinition($name, $data);

        return $s->toArray();
    }

    private function basicModuleOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired('section');

        return $resolver;
    }
}
