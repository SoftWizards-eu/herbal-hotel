<?php
namespace w3des\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Settings;
use w3des\AdminBundle\Event\AdminMenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminMenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $factory;

    private $settings;

    private $nodes;

    private $stack;

    private $tranlator;

    private $eventDispatcher;

    public function __construct(FactoryInterface $factory, Settings $sett, Nodes $nodes, RequestStack $stack, TranslatorInterface $tranlator, EventDispatcherInterface $dispatcher)
    {
        $this->factory = $factory;
        $this->settings = $sett;
        $this->nodes = $nodes;
        $this->stack = $stack;
        $this->tranlator = $tranlator;
        $this->eventDispatcher = $dispatcher;
    }

    public function createMenu(array $options)
    {
        $menu = $this->factory->createItem('root')->setExtra('translation_domain', 'admin');

        $menu->addChild($this->trans('dashboard'), array(
            'route' => 'admin.home'
        ))
            ->setExtra('ico', 'home1');


        $menu->addChild($this->trans('admin.users'), array(
            'route' => 'admin.users'
        ))
            ->setExtra('ico', 'users1')
            ->setExtra('routes', [
            [
                'route' => 'admin.users.add'
            ],
            [
                'route' => 'admin.users'
            ],
            [
                'route' => 'admin.users.edit'
            ]
        ]);
 
        foreach ($this->nodes->getCfg() as $name => $cfg) {
            $routes = [];
            if (! $cfg['enabled']) {
                continue;
            }
            $sett = $menu->addChild($this->trans('node.' . $name), [
                'route' => 'admin.node',
                'routeParameters' => [
                    'type' => $name
                ],
                'extras' => [
                    'ico' => $cfg['icon']
                ]
            ]);

            $request = $this->stack->getMainRequest();
            if ($request && \strpos($request->attributes->get('_route'), 'admin.node') === 0 && $request->attributes->get('type') == $name) {
                $sett->setCurrent(true);
            }
        }

       $this->eventDispatcher->dispatch(new AdminMenuEvent($this->factory, $menu), AdminMenuEvent::ADMIN_MENU);

        $sett = null;
        $routes = [];
        foreach (array_keys($this->settings->getSections()) as $name) {
            if (! $sett) {
                $sett = $menu->addChild($this->trans('settings'), [
                    'route' => 'admin.settings',
                    'routeParameters' => [
                        'group' => $name
                    ]
                ])
                    ->setExtras([
                    'ico' => 'filter1'
                ]);
            }
            $sett->addChild($this->trans('settings.' . $name), [
                'route' => 'admin.settings',
                'routeParameters' => [
                    'group' => $name
                ]
            ])
                ->setExtras([
                'translation_domain' => 'admin'
            ]);
            $routes[] = [
                'route' => 'admin.settings',
                'routeParameters' => [
                    'group' => $name
                ]
            ];
        }
        $sett->setExtra('routes', $routes);

        return $menu;
    }

    protected function trans($label)
    {
        return $this->tranlator->trans($label, [], 'admin');
    }
}

