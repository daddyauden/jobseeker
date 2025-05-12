<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class LocaleController extends AdminBase
{

    protected $entityName = "AdminBundle:Locale";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 2;
        $pager = $this->generatePager("/admin/locale-list-%d", $page, $offset, count($this->getCache()));

        return $this->render('admin/locale/list.html.twig', array('locales' => $this->createLocaleFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("locale_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => false, "label" => $this->trans("table.locale.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("code", TextType::class, array("label" => $this->trans("table.locale.code"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.locale.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.locale.status.1") => 1,
                    $this->trans("table.locale.status.0") => 0
                ),
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                'data' => 0
            ))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                if (null !== $this->getRepo()->findOneBy(array("code" => $entity->getCode()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getCode()));
                } else {
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("locale_list"));
                }
            }
        }

        return $this->render("admin/locale/add.html.twig", array("form" => $form->createView()));
    }

    public function saveAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $locale = json_decode($this->getRequest()->getContent(), true);
                $id = $locale['id'];
                unset($locale['id']);
                $entity = $this->getRepo($this->getEntityName())->find($id);
                $oldentity = clone $entity;
            } else {
                $locale = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($locale['queue']);
            $entity->setCode($locale['code']);
            $entity->setStatus($locale['status']);
            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->flush();
                        $this->dumpCache();
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
            } else {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                return $this->redirect($this->generateUrl("locale_list"));
            }
        }
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $localeId = $this->getRequest()->getContent();
                $entity = $this->getRepo($this->getEntityName())->find($localeId);
                try {
                    $this->getEm()->remove($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->render("empty.html.twig", array("status" => "success"));
                } catch (\Exception $e) {
                    return $this->render("empty.html.twig", array("status" => "error"));
                }
            }
        }
    }

    private function createLocaleFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $locales = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($locales) > 0) {
            foreach ($locales as $locale) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $locale['id']))
                    ->add("queue", NumberType::class, array("required" => false, "data" => $locale['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("code", TextType::class, array(
                        "data" => $locale['code'],
                        "label" => $this->trans(strtolower($locale['code'] . ".title")),
                        "attr" => array(
                            "class" => "form-control input-sm",
                            "title" => strtolower($this->trans($locale['code']))
                        )
                    ))
                    ->add("status", ChoiceType::class, [
                        "attr" => [
                            "class" => "form-control input-sm"
                        ],
                        "choices" => [
                            $this->trans("table.locale.status.1") => 1,
                            $this->trans("table.locale.status.0") => 0
                        ],
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $locale["status"]
                    ])
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    final public function dumpCache()
    {
        // for locale
        $locales = array();
        $localeEntity = $this->getRepo("AdminBundle:Locale")->getAll();
        if (count($localeEntity) > 0) {
            foreach ($localeEntity as $locale) {
                $locales[$locale['id']] = $locale;
                $locales[$locale['id']]['alias'] = $this->trans(strtolower($locale['code'] . ".title"));
            }
        }
        parent::buildCache($locales, "AdminBundle:Locale");
    }

}
