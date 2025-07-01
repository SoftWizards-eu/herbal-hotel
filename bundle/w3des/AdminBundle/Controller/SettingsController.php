<?php
namespace w3des\AdminBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Setting;
use w3des\AdminBundle\Service\Settings;
use w3des\AdminBundle\Service\Values;
use w3des\AdminBundle\Form\Type\ValuesType;
use w3des\AdminBundle\Model\SettingsList;
use w3des\AdminBundle\Service\CMS;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{

    private $cms;
    private $em;

    public function __construct(CMS $cms, EntityManagerInterface $em)
    {
        $this->cms = $cms;
        $this->em = $em;
    }

    /**
     * @Route("/{group}", name="admin.settings")
     */
    public function groupAction($group, $pageLocale, Request $request, Settings $sett, Values $values)
    {
        if (! isset($sett->getSections()[$group])) {
            throw $this->createNotFoundException();
        }

        $sectionFields = $sett->getSections()[$group];
        $definitions = [];
        foreach ($sectionFields as $key => $name) {
            if (is_array($name)) {
                foreach ($name as $sub) {
                    $definitions[$sub] = $sett->getField($sub);
                }
            } else {
                $definitions[$name] = $sett->getField($name);
            }
        }
        $list = $this->em->getRepository(Setting::class)->findByNames($this->cms->getService(), \array_keys($definitions), [
            Values::MODEL_DEFAULT_LOCALE,
            $pageLocale
        ]);
        $models = new SettingsList($list);
        $form = $this->createForm(ValuesType::class, $models, [
            'label_prefix' => 'settings.',
            'fields' => $definitions,
            'locales' => [
                $pageLocale
            ],
            'translation_domain' => 'admin',
            'value_type' => function() {
                $tmp = new Setting();
                $tmp->setService($this->cms->getService());

                return $tmp;
            }
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($models as $m) {
                $this->em->persist($m);
            }
            foreach ($models->getRemoved() as $m) {
                $this->em->remove($m);
            }
            $this->em->flush();
            $request->getSession()
                ->getFlashBag()
                ->set('info', 'Zapisano pomyślnie');

            return $this->redirect($this->generateUrl('admin.settings', [
                'group' => $group
            ]));
        }

        return $this->render('@w3desAdmin/Settings/group.html.twig', [
            'group' => $group,
            'form' => $form->createView()
        ]);
    }
}

