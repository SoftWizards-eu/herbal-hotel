<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Node;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\Response;
use w3des\AdminBundle\Entity\NodeUrl;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Routing\Annotation\Route;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Service\ModuleRegistry;
use w3des\AdminBundle\Service\CMS;
use App\Twig\AppExtension;
use w3des\AdminBundle\Controller\NodesController;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Bundle\MenuBundle\Templating\Helper\MenuHelper;
use Knp\Menu\Twig\Helper;

/**
 * @author zulus
 */
class NodeController extends AbstractController
{

    private $nodes;

    public function __construct(Nodes $nodes)
    {
        $this->nodes = $nodes;
    }


    /**
     * @ParamConverter("node", converter="node")
     */
    public function node(Request $request, ModuleRegistry $registry, NodeView $node, CMS $cms, MenuProviderInterface $menu)
    {
        $cfg = $this->nodes->getNodeCfg($node->type);

        $modules = [

        ];
        $moduleTypes = [];

        foreach ($node->modules as $section => $list) {
            $modules[$section] = [];
            foreach ($list as $data) {
                $result = $registry->getModule($data->type)->frontend($request, $data, $cfg['modules'][$section]['options'][$data->type]['options']);
                if ($result instanceof Response) {
                    if ($result->getStatusCode() == 200) {
                        $modules[$section][] = $result->getBody();
                    } else {
                        return $result;
                    }
                } elseif ($result === null) {
                    continue;
                } else {
                    $modules[$section][] = $this->renderView(\str_replace('module.', 'nodes/', $data->type) . '.html.twig', $result);
                    $moduleTypes[] = $data->type;
                }
            }
        }
        $cms['module_types'] = $moduleTypes;
        if ($cfg['redirectEmpty'] && count($modules[$cfg['redirectEmpty']]) == 0) {
            foreach ($node->children as $child) {
                if ($child->url && (!$child->vars->has('public') || $child->vars->public)) {
                    return $this->redirect($child->url);
                }
            }
            $menu->get('app.any');
            if ($request->attributes->has('_menu')) {
                /** @var \Knp\Menu\ItemInterface $current */
                $current = $request->attributes->get('_menu');
                if ($current && $current->hasChildren()) {
                    return $this->redirect($current->getFirstChild()->getUri());
                }
            }
        }


        return $this->render('cms/node_' . $node->type . '.html.twig', [
            'node' => $node,
            'modules' => $modules
        ]);
    }

    /**
     * @Route("/_download/{id}", name="download")
     * @Method("GET")
     */
    public function download(Node $node)
    {
        if ($node->getType() != 'file') {
            throw $this->createAccessDeniedException();
        }
        $response = new BinaryFileResponse($this->getParameter('upload.path') . $this->nodes->getVariable($node, 'file')['path']);
        $response->setContentDisposition('attachment', $this->nodes
            ->getVariable($node, 'name'));

        return $response;
    }

    /**
     * @Route("/{_locale}/search", name="search")
     */
    public function search(AppExtension $ext, Request $request)
    {
        if (strlen(trim($request->query->get('query'))) < 3) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render('cms/search.html.twig', [
            'search' => $this->nodes->search($request->query->get('type') ? [$request->query->get('type')] : ['page', 'offer', 'news', 'realization', 'blog', 'press'], [
                'page' => $request->query->get('page', 1),
                'max' => 5,
                'search' => $request->query->get('query'),
                'locale' => $request->getLocale(),
                'pagging' => true
            ])
        ]);
    }
}