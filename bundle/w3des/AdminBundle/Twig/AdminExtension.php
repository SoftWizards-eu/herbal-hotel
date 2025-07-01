<?php
namespace w3des\AdminBundle\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Service\ModuleRegistry;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Settings;
use w3des\AdminBundle\Model\ModuleInfo;

class AdminExtension extends AbstractExtension
{

    private Settings $settings;

    private Nodes $nodes;

    private ModuleRegistry $registry;

    public function __construct(Settings $settings, Nodes $nodes, ModuleRegistry $registry)
    {
        $this->settings = $settings;
        $this->nodes = $nodes;
        $this->registry = $registry;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sett', [
                $this,
                'getSetting'
            ]),
            new TwigFunction('load_nodes', [
                $this,
                'getNodes'
            ]),
            new TwigFunction('load_node', [
                $this,
                'getNode'
            ]),
            new TwigFunction('date_range', [
                $this,
                'dateRange'
            ])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('file', [
                $this,
                'getFilePath'
            ]),
            new TwigFilter('youtube_embed', [
                $this,
                'getYoutubeEmbed'
            ]),
            new TwigFilter('render_module', [
                $this,
                'getRenderModule'
            ], [
                'is_safe' => array(
                    'html',
                    'js',
                    'css'
                )
            ])
        ];
    }

    public function getSetting($name, $default = null, $locale = null)
    {
        return $this->settings->get($name, $default, $locale);
    }

    public function getRenderModule(ModuleInfo $info)
    {
        throw new \InvalidArgumentException();
    }

    public function getNodes($type, $cfg = [])
    {
        return $this->nodes->getNodes($type, $cfg);
    }

    public function getNodeUrl($node)
    {
        return $this->nodes->getUrl($node);
    }

    public function getFilePath($file)
    {
        if (! $file) {
            return null;
        }

        return 'uploads/' . $file->getPath();
    }

    public function getNode($type, $cfg = [])
    {
        $cfg['max'] = 1;
        $res = $this->nodes->getNodes($type, $cfg);
        if (count($res['list'])) {
            return \array_shift($res['list']);
        }

        return null;
    }

    public function getYoutubeEmbed($url)
    {
        $matches = [];
        preg_match('#v=([^&]*$)#i', $url, $matches);
        return '//www.youtube.com/embed/' . $matches[1] . '?version=3&autoplay=1';
    }

    public function dateRange(\DateTime $from, \DateTime $to = null)
    {
        if ($to) {
            if ($from->format('d.m.Y') != $to->format('d.m.Y')) {
                if ($from->format('m') == $to->format('m') && $from->format('Y') == $to->format('Y')) {
                    return $from->format('d') . '-' . $to->format('d') . $to->format('.m.Y');
                } else {
                    return $from->format('d.m.Y') . ' - ' . $to->format('d.m.Y');
                }
            } else {
                return $from->format('d.m.Y');
            }
        } else {
            return $from->format('d.m.Y');
        }
    }
}

