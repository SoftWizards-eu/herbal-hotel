<?php
namespace App\Menu;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\HttpFoundation\RequestStack;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Contracts\Translation\TranslatorInterface;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Model\NodeView;

class MenuBuilder
{

    protected $stack;

    protected $em;

    private $translator;

    public function __construct(FactoryInterface $factory, Nodes $nodes, RequestStack $stack, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->nodes = $nodes;
        $this->stack = $stack;
        $this->em = $em;
        $this->nodes = $nodes;
        $this->translator = $translator;
    }

    public function createMenu(array $options)
    {
        $menu = $this->factory->createItem('Strona główna', [
            'route' => 'homepage'
        ])->setExtra('translation_domain', 'messages');
        $this->add($menu, $this->nodes->getNodes('menu', [
            'parent' => null
        ])['list']);

        return $menu;
    }

    public function createFooter(array $options)
    {
        $menu = $this->factory->createItem('Strona główna', [
            'route' => 'homepage'
        ])->setExtra('translation_domain', 'messages');

        $list =   $this->nodes->getNodes('footer', [
                'parent' => null
            ])['list'];            
            
        $this->add($menu, $list);

        return $menu;
    }

    public function createAny(array $options)
    {
        $menu = $this->factory->createItem('Strona główna', [
            'route' => 'homepage'
        ])->setExtra('translation_domain', 'messages');
        $this->add($menu, $this->nodes->getNodes('menu', [
            'parent' => null
        ])['list']);
        $this->add($menu, $this->nodes->getNodes('footer', [
            'parent' => null
        ])['list']);

        return $menu;
    }

    public function createCategoryMenu(array $options)
    {
        $menu = $this->factory->createItem('root')->setExtra('translation_domain', 'messages');
        $this->add($menu, $this->em->createQuery('select n from w3desAdminBundle:Node n where n.type = :type and n.locale = :locale and n.parent is null order by n.pos')
            ->execute([
            'type' => 'category',
            'locale' => $this->stack->getMainRequest()
                ->getLocale()
        ]));

        return $menu;
    }

    private function add(MenuItem $menu, $list)
    {
        /** @var \w3des\AdminBundle\Model\NodeView $item */
        foreach ($list as $item) {
            $target = $item->vars->node;

            if ($target == null || ! $target->vars->public) {
                continue;
            }
            $url = $target->url;
            if($target->vars->external_url != null && trim($target->vars->external_url)!=''){
                $url = $target->vars->external_url;
            }
 
            $ch = $menu->addChild($item->id . '', [
                'label' => $item->vars->name,
                'uri' => $url
            ]);
            if ($this->stack->getMainRequest()->getRequestUri() == $url) {
                $ch->setCurrent(true);
                if (!$this->stack->getMainRequest()->attributes->has('_menu')) {
                    $this->stack->getMainRequest()->attributes->set('_menu', $ch);
                }
            }
            $this->add($ch, $item->children);
            $curr = $this->stack->getMainRequest()->attributes->get('node');

            foreach ($target->modules['content'] as $mod) {
                if ($mod->type == 'module.list') {
                    if ($mod->vars->type == 'offer') {
                        $this->addOffer($ch);
                    } elseif ($curr instanceof NodeView && $mod->vars->type == $curr->type) {
                        $ch->setCurrent(true);
                    }
                }
            }
        }
    }

    private function addOffer(MenuItem $menu)
    {
        foreach ($this->nodes->getNodes('offer')['list'] as $target) {
            if ($target == null || ! $target->vars->public) {
                continue;
            }
            $url = $target->url;
            $ch = $menu->addChild($target->id . '', [
                'label' => $target->vars->title,
                'uri' => $url
            ]);
            if ($this->stack->getMainRequest()->getRequestUri() == $url) {
                $ch->setCurrent(true);
            }
        }
    }
}

