<?php
namespace w3des\AdminBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Node;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use w3des\AdminBundle\Service\CMS;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NodeConverter implements ParamConverterInterface
{

    private Nodes $nodes;

    private CMS $cms;

    /**
     * @param EntityManager $em
     */
    public function __construct(Nodes $nodes, CMS $cms)
    {
        $this->nodes = $nodes;
        $this->cms = $cms;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $res = $this->nodes->getByPath($request->attributes->get('path'), $request->getLocale());
        if (!$res && !$configuration->isOptional()) {
            throw new NotFoundHttpException();
        }
        $request->attributes->set($configuration->getName(), $res);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() == NodeView::class;
    }
}

