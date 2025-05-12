<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class SystemController extends AdminBase
{

    protected $entityName = "AdminBundle:System";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 2;
        $pager = $this->generatePager("/admin/system-list-%d", $page, $offset, count($this->getCache()));

        return $this->render('admin/system/list.html.twig', array('systems' => $this->createSystemFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function createSystemFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $systems = array_slice($this->getCache(), ($page - 1) * $offset, $offset);

        if (count($systems) > 0) {
            foreach ($systems as $system) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $system['id']))
                    ->add("skey", TextType::class, array(
                        "data" => $system['skey'],
                        "attr" => array(
                            "class" => "form-control input-sm"
                        )
                    ))
                    ->add("stype", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => $this->createStypeChoice(),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $system['stype']
                    ))
                    ->add("svalue", $system['stype'] === "area" || $system['stype'] === "locale" || $system['stype'] === "currency" ? ChoiceType::class : TextType::class, $this->detectType($system['stype'], array("data" => $system['svalue'], "attr" => array("class" => "form-control input-sm"))))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("system_add"))
            ->setMethod("post")
            ->add("skey", TextType::class, array("required" => true, "label" => $this->trans("table.system.skey"), "attr" => array("class" => "form-control input-sm", "autofocus" => "")))
            ->add("stype", ChoiceType::class, array(
                "label" => $this->trans("table.system.stype"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->createStypeChoice(),
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                'data' => "string"
            ))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                if (null !== $this->getRepo()->findOneBy(array("skey" => $entity->getSkey()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getSkey()));
                } else {
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("system_list"));
                }
            }
        }

        return $this->render("admin/system/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $localeId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($localeId);
                $skey = $entity->getSkey();
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

    public function saveAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $system = json_decode($this->getRequest()->getContent(), true);
                $id = $system['id'];
                unset($system['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $system = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setSkey(strtolower($system['skey']));
            $entity->setStype(strtolower($system['stype']));
            if (isset($system['svalue'])) {
                $entity->setSvalue($system['svalue']);
            }
            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->persist($entity);
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
                return $this->redirect($this->generateUrl("system_list"));
            }
        }
    }

    private function createStypeChoice()
    {
        return array(
            $this->trans("table.system.stype.text") => "text",
            $this->trans("table.system.stype.textarea") => "textarea",
            $this->trans("table.system.stype.number") => "number",
            $this->trans("table.system.stype.currency") => "currency",
            $this->trans("table.system.stype.locale") => "locale",
            $this->trans("table.system.stype.area") => "area"
        );
    }

    private function detectType($type, $default = array())
    {

        $data = array();
        switch ($type) {
            case "area":
                $data["choices"] = $this->getAvailableCountry();
                break;
            case "locale":
                $data["choices"] = $this->getAvailableLanguage();
                break;
            case "currency":
                $data['choices'] = $this->getAvailableCurrency();
                break;
        }

        return array_merge($default, $data);
    }

    private function getAvailableCountry()
    {
        $data = array();
        $result = $this->getCache("AdminBundle:Area");
        if (count($result) < 1) {
            $result = $this->getRepo("AdminBundle:Area")->getAvailableCountry();
        }

        foreach ($result as $value) {
            if ((int)$value['level'] === 2) {
                $data[$this->trans(strtoupper($value['code']))] = strtoupper($value['code']);
            }
        }
        return $data;
    }

    private function getAvailableLanguage()
    {
        $data = array();
        $result = $this->getCache("AdminBundle:Locale");
        if (count($result) < 1) {
            $result = $this->getRepo("AdminBundle:Locale")->getAvailable();
        }

        foreach ($result as $value) {
            if (intval($value['status']) === 1) {
                $data[$this->trans(strtolower($value['code']) . ".title")] = $value["code"];
            }
        }

        return $data;
    }

    private function getAvailableCurrency()
    {
        $data = array();
        $result = $this->getCache("AdminBundle:Currency");
        if (count($result) < 1) {
            $result = $this->getRepo("AdminBundle:Currency")->getAvailable();
        }

        foreach ($result as $value) {
            if (intval($value['status']) === 1) {
                $data[strtoupper($value['code']) . " (" . $value['symbol'] . ") - " . $this->trans(strtolower($value['code']) . ".title")] = strtolower($value["code"]);
            }
        }

        return $data;
    }

    final public function dumpCache()
    {
        // for system
        $systems = array();
        $systemEntity = $this->getRepo("AdminBundle:System")->getAll();
        if (count($systemEntity) > 0) {
            foreach ($systemEntity as $system) {
                $systems[$system['skey']] = array(
                    "id" => $system['id'],
                    'skey' => $system['skey'],
                    'stype' => $system['stype'],
                    'svalue' => $system['stype'] == "number" ? (int)$system['svalue'] : $system['svalue']
                );
            }
            $this->setSystem();
        }

        parent::buildCache($systems, "AdminBundle:System");
    }

}
