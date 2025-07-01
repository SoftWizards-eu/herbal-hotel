<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Form\FormFactoryInterface;
use w3des\AdminBundle\Form\Type\ContactType;
use w3des\AdminBundle\NodeModule\ContactModule;
use w3des\AdminBundle\Model\NodeView;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Entity\Node;
use App\NodeModule\OfferFormModule;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

    private $factory;
    private $contact;
    private $nodes;
    private $offer;

    public function __construct(FormFactoryInterface $factory, ContactModule $contact, OfferFormModule $offer, Nodes $nodes)
    {
        $this->factory = $factory;
        $this->contact = $contact;
        $this->nodes = $nodes;
        $this->offer = $offer;
    }



    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('contact_form', [
                $this,
                'getContactForm'
            ]),
            new TwigFunction('offer_form', [
                $this,
                'getOfferForm'
            ])
        ];
    }

    public function getContactForm()
    {
        $nv = new NodeView($this->nodes);
        $nv->model = new Node();
        $nv->model->setType('module.contact_form');
        return $this->contact->getForm($nv)->createView();
    }

    public function getOfferForm($type = 'offer')
    {
        $nv = new NodeView($this->nodes);
        $nv->model = new Node();
        $nv->model->setType('module.offer_form');
        return $this->offer->getForm($nv, $type)->createView();
    }

    public function autoBlank($content)
    {
        \preg_match_all('#<a.*?href="(.*?)".*?>.*?</a>#i', $content, $match);

        foreach ($match[1] as $id => $v) {
            if ($v[0] == '/') {
                continue;
            }
            if (parse_url($v, PHP_URL_HOST) != $_SERVER['HTTP_HOST']) {
                $n = str_replace('href="' . $v . '"', 'href="' . $v . '" target="_BLANK" rel="nofollow"', $match[0][$id]);
                $content = \str_replace($match[0][$id], $n, $content);
            }
        }
        return $content;
    }
    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('auto_blank', [
                $this,
                'autoBlank'
            ])
        ];
    }

}

