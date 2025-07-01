<?php
namespace w3des\AdminBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeVariable;
use Doctrine\ORM\Query\Expr\Join;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;
use Doctrine\ORM\EntityManagerInterface;
use w3des\AdminBundle\Form\Type\FullNodeType;
use w3des\AdminBundle\Service\CMS;

/**
 * @Route("/nodes")
 */
class NodesController extends AbstractController
{

    private $nodes;

    private $values;

    private $em;

    private $pageLocales;

    private $cms;

    public function __construct(CMS $cms, Nodes $nodes, Values $values, EntityManagerInterface $em, array $pageLocales)
    {
        $this->nodes = $nodes;
        $this->values = $values;
        $this->em = $em;
        $this->pageLocales = $pageLocales;
        $this->cms = $cms;
    }

    /**
     * @Route("/{type}/list/{embedType}", name="admin.node.link", methods="GET")
     */
    public function linkAction($type, $embedType, $pageLocale, Request $request)
    {
        $cfg = $this->nodes->getNodeCfg($embedType);
        $repo = $this->em->getRepository(Node::class);
        $qb = $repo->createQueryBuilder('a');
        $qb->andWhere('a.type = :type and a.locale = :locale and a.service = :service and a.parent is null')
            ->setParameter('type', $embedType)
            ->setParameter('locale', $cfg['locale'] ? $pageLocale : 'xx')
            ->setParameter('service', $this->cms->getService());
        $qb->join('a.variables', 'v');
        $qb->orderBy('a.pos', 'ASC');

        return $this->render('@w3desAdmin/Nodes/link.html.twig', [
            'items' => $cfg['sortable'] ? $this->buildTree($cfg, $qb->getQuery()
                ->execute()) : [],
            'cfg' => $cfg,
            'fields' => $this->nodes->getFields($embedType),
            'type' => $embedType,
            'target' => $type,
            'values' => $this->values
        ]);
    }

    /**
     * @Route("/{type}/list", name="admin.node", methods="GET")
     */
    public function indexAction($type, $pageLocale, Request $request)
    {
        $cfg = $this->nodes->getNodeCfg($type);

        $repo = $this->em->getRepository(Node::class);
        $qb = $repo->createQueryBuilder('a');
        $qb->andWhere('a.type = :type and a.locale = :locale and a.service = :service and a.parent is null')
            ->setParameter('type', $type)
            ->setParameter('locale', $cfg['locale'] ? $pageLocale : 'xx')
            ->setParameter('service', $this->cms->getService());
        $qb->join('a.variables', 'v');
        $qb->orderBy('a.pos', 'ASC');

        return $this->render('@w3desAdmin/Nodes/index.html.twig', [
            'items' => $cfg['sortable'] ? $this->buildTree($cfg, $qb->getQuery()
                ->execute(), $pageLocale) : [],
            'cfg' => $cfg,
            'fields' => $this->nodes->getFields($type),
            'type' => $type,
            'values' => $this->values
        ]);
    }

    private function buildTree($cfg, $list, $pageLocale, $lvl = 1)
    {
        $data = [];
        foreach ($list as $item) {
            $tmp = [
                'title' => $this->nodes->getVariable($item, $cfg['title'], $pageLocale),
                'isLeaf' => $lvl >= $cfg['maxDepth'] && false,
                'isExpanded' => true,
                'data' => [
                    'id' => $item->getId(),
                    'add' => $this->generateUrl('admin.node.add', [
                        'type' => $item->getType(),
                        'parent' => $item->getId()
                    ]),
                    'edit' => $this->generateUrl('admin.node.edit', [
                        'type' => $item->getType(),
                        'id' => $item->getId()
                    ]),
                    'remove' => $this->generateUrl('admin.node.remove', [
                        'type' => $item->getType(),
                        'id' => $item->getId()
                    ]),
                    'embed' => $this->generateUrl('admin.node.add', [
                        'type' => $item->getType(),
                        'parent' => $item->getId(),
                        'embedType' => '__type__'
                    ]),
                    'link' => $this->generateUrl('admin.node.link', [
                        'type' => $item->getType(),
                        'parent' => $item->getId(),
                        'embedType' => '__type__'
                    ]),
                ],
                'children' => $this->buildTree($cfg, $item->getChildren(),$pageLocale, $lvl + 1)
            ];
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * @Route("/{type}/list", methods="POST")
     */
    public function saveOrderAction($type, $pageLocale, Request $request)
    {
        $em = $this->em;
        $repo = $em->getRepository(Node::class);
        $em->beginTransaction();
        foreach ($request->toArray()['tree'] as $id => $tr) {
            $tmp = $repo->find($id);
            if ($tr['parent'] && $tr['parent'] !== null) {
                $tmp->setParent($em->getReference(Node::class, $tr['parent']));
            } else {
                $tmp->setParent(null);
            }
            $tmp->setPos($tr['pos']);
            $em->persist($tmp);
            $em->flush();
            $em->detach($tmp);
        }
        $em->commit();

        $request->getSession()
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');
        return $this->redirect($this->generateUrl('admin.node', [
            'type' => $type
        ]));
    }

    /**
     * @Route("/{type}.json", name="admin.node.json")
     */
    public function jsonAction($type, $pageLocale, Request $request)
    {
        $cfg = $this->nodes->getNodeCfg($type);
        $fields = $this->nodes->getFields($type);
        $repo = $this->em->getRepository(Node::class);
        $qb = $repo->createQueryBuilder('a');
        $qb->andWhere('a.type = :type and a.locale = :locale and a.service = :service')
            ->setParameter('type', $type)
            ->setParameter('locale', $cfg['locale'] ? $pageLocale : 'xx')
            ->setParameter('service', $this->cms->getService());
        $num = 0;
        foreach ($cfg['grid'] as $field) {
            $def = $fields[$field];
            if ($request->query->get($field) || $request->query->get('orderBy') == $field) {
                $val = $request->query->get($field);
                $qb->innerJoin('a.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\'');
                if ($request->query->has($field)) {
                    if ($def['storeType'] == 'string' || $def['storeType'] == 'text') {
                        $val = '%' . trim($val) . '%';
                        $qb->andWhere('lower(v_' . $field . $num . '.' . NodeVariable::getFieldName($def['storeType']) . ') like lower(:val' . $num . ')')->setParameter('val' . $num, $val);
                    } else {
                        if ($def['storeType'] == 'bool') {
                            $val = $val == 'true' ? 1 : 0;
                        }
                        $qb->andWhere('v_' . $field . $num . '.' . NodeVariable::getFieldName($def['storeType']) . ' = :val' . $num)->setParameter('val' . $num, $val);
                    }
                }
                if ($request->query->get('orderBy') == $field) {
                    if ($def['storeType'] == 'string' || $def['storeType'] == 'text') {
                        $qb->orderBy('lower(v_' . $field . $num . '.' . NodeVariable::getFieldName($def['storeType']) . ')', $request->query->get('desc') == 'false' ? 'asc' : 'desc');
                    } else {
                        $qb->orderBy('v_' . $field . $num . '.' . NodeVariable::getFieldName($def['storeType']), $request->query->get('desc') == 'false' ? 'asc' : 'desc');
                    }
                }
            }

            $num ++;
        }

        if ($request->get('id') > 0) {
            $qb->andWhere('a.id = :id')->setParameter('id', (int) $request->get('id'));
        }
        if ($request->get('orderBy') == 'id') {
            $qb->orderBy('a.id', $request->query->get('desc') == 'false' ? 'asc' : 'desc');
        }

        $qb->join('a.variables', 'v')->addSelect('v');

        $res = [];
        $vals = $this->values;
        $paginator = new Paginator($qb->getQuery(), true);
        $response = [
            'total' => count($paginator),
            'pageSize' => (int) $request->get('pageSize'),
            'pageNo' => (int) $request->get('pageNo')
        ];
        $paginator->getQuery()
            ->setMaxResults($request->get('pageSize'))
            ->setFirstResult($request->get('pageSize') * ($request->get('pageNo') - 1));

        foreach ($paginator as $n) {
            $data = [
                'id' => $n->getId()
            ];
            foreach ($cfg['sections'] as $sec) {
                foreach ($cfg['grid'] as $name) {
                    $v = $this->nodes->getVariable($n, $name, $pageLocale);
                    if ($v) {
                        if ($v instanceof \DateTime) {
                            $v = $v->format('Y-m-d H:i');
                        }
                        $data[$name] = $v;
                    } else {
                        $data[$name] = null;
                    }
                }
            }
            $res[] = $data;
        }
        $response['data'] = $res;

        return new JsonResponse($response);
    }

    /**
     * @Route("/{type}/add", name="admin.node.add")
     */
    public function addAction($type, Request $request)
    {
        $model = new Node();
        $model->setService($this->cms->getService());
        $model->setType($type);
        $cfg = $this->nodes->getNodeCfg($type);
        $model->setLocale($cfg['locale'] ? $request->attributes->get('_page_locale') : 'xx');
        $model->setPos(0);

        if ($request->query->has('parent')) {
            $model->setParent($this->getDoctrine()
                ->getManager()
                ->getReference(Node::class, $request->query->get('parent')));
        }
        if ($cfg['sortable']) {
            $max = $this->getDoctrine()
                ->getManager()
                ->createQuery('select max(n.pos) from w3desAdminBundle:Node n where n.type = :type and n.service = :service and n.parent = :parent and n.locale = :locale')
                ->setParameters([
                    'service' => $model->getService(),
                    'parent' => $model->getParent() ? $model->getParent() : null,
                    'type' => $model->getType(),
                    'locale' => $model->getLocale()
                ])
                ->getSingleScalarResult();
            $model->setPos($max + 1);
        }
        if ($request->query->get('embedId')) {
            $tmp = new NodeVariable();
            $tmp->setName($cfg['embed']['field']);
            $tmp->setType('node');
            $tmp->setValue($this->getDoctrine()
                ->getRepository(Node::class)
                ->find($request->query->get('embedId')));
            $tmp->setPos(0);
            $tmp->setLocale($cfg['fields'][$cfg['embed']['field']]['locale'] ? $this->cms->getLocale() : Values::MODEL_DEFAULT_LOCALE);
            $tmp->setNode($model);
            $model->getVariables()->add($tmp);
        }

        return $this->form($request, $type, $model);
    }

    /**
     * @Route("/{type}/{id}/edit", name="admin.node.edit")
     */
    public function editAction(Node $node, $type, Request $request)
    {
        return $this->form($request, $type, $node);
    }

    /**
     * @Route("/{type}/{id}/remove", name="admin.node.remove")
     */
    public function removeAction(Node $node, $type, Request $request)
    {
        $em = $this->em;
        $em->beginTransaction();

        $em->remove($node);
        $em->flush();
        $em->commit();

        $request->getSession()
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');

        return $this->redirect($this->generateUrl('admin.node', [
            'type' => $type
        ]));
    }

    protected function form(Request $request, $type, Node $node)
    {
        $cfg = $this->nodes->getNodeCfg($type);

        $form = $this->createForm(FullNodeType::class, $node, [
            'type' => $type,
            'locales' => [
                $request->attributes->get('_page_locale')
            ],
            'embed' => $request->get('embedType'),
            'sections' => true,
            'translation_domain' => 'admin'
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->beginTransaction();
            $this->em->persist($node);
            $this->em->flush();
            //
            $this->em->commit();
            $request->getSession()
                ->getFlashBag()
                ->set('info', 'Zapisano pomyślnie');

            if ($request->request->get('save') == 'close') {
                return $this->redirect($this->generateUrl('admin.node', [
                    'type' => $type
                ]));
            }

            return $this->redirect($this->generateUrl('admin.node.edit', [
                'type' => $type,
                'id' => $node->getId()
            ]) . $request->get('tab_position'));
        }

        return $this->render('@w3desAdmin/Nodes/form.html.twig', [
            'type' => $type,
            'cfg' => $cfg,
            'node' => $this->nodes->wrap($node),
            'form' => $form->createView()
        ]);
    }
}

