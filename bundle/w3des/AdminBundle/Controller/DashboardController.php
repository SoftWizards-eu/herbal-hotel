<?php
namespace w3des\AdminBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class DashboardController extends AbstractController
{

    /**
     * @Route("/", name="admin.home")
     */
    public function indexAction()
    {
        return $this->render('@w3desAdmin/Dashboard/index.html.twig');
    }

    /**
     * @Route("/service/{id}", name="admin.service")
     */
    public function service(Request $request, $id)
    {
        $request->getSession()->set('_service', $id);

        return $this->redirect($this->generateUrl('admin.home'));
    }

    /**
     * @Route("/lang/{lang}", name="admin.lang")
     */
    public function langAction(Request $request, $lang)
    {
        $request->getSession()->set('_page_locale', $lang);

        return $this->redirect($this->generateUrl('admin.home'));
    }

    /**
     * @Route("/upload", name="admin.upload", methods="POST")
     */
    public function uploadAction(Request $request, CacheManager $cacheManager, $uploadPath, $publicDir)
    {
        $uploadDir = $publicDir . '/' . $uploadPath;
        $dir = '/' . $request->request->get('dir') . '/' . date('Y') . '/' . date('m') . '/' . date('d');
        if (! \file_exists($uploadDir . $dir)) {
            \mkdir($uploadDir . $dir, 0777, true);
        }
        $tmp = new Slugify();
        $result = [];
        foreach ($request->files->get('files') as $f) {
            if ($f instanceof UploadedFile) {
                $orig = $source = $f->getClientOriginalName();
                if ($f->getClientOriginalExtension()) {
                    $source = substr($source, 0, strlen($source) - 1 - strlen($f->getClientOriginalExtension()));
                }
                $name = \uniqid('', true) . '_' . $tmp->slugify($source) . '.' . \strtolower($f->getClientOriginalExtension());
                $f->move($uploadDir . $dir, $name);
                \chmod($uploadDir . $dir . '/' . $name, 0666);
                $f = new \SplFileInfo($uploadDir . $dir . '/' . $name);
                $result[] = [
                    'path' => $uploadPath . $dir . '/' . $name,
                    'name' => $name,
                    'origName' => $orig,
                    'thmb' => $cacheManager->getBrowserPath($uploadPath . $dir . '/' . $name, 'admin'),
                    'size' => $f->getSize(),
                    'url' => $request->getBaseUrl() . $uploadPath . $dir . '/' . $name,
                    'deleteUrl' => false,
                    'deleteType' => 'DELETE'
                ];
            }
        }

        return new JsonResponse([
            'files' => $result
        ]);
    }

}
