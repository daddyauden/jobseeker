<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class DiplomaController extends AdminBase
{

    protected $entityName = "UserBundle:Category";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/diploma-list-%d", $page, $offset, count($this->getCache("UserBundle:Employee_Diploma")));

        return $this->render("admin/diploma/list.html.twig", array("diplomas" => $this->createDiplomaFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("diploma_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => true, "label" => $this->trans("table.diploma.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("csn", TextType::class, array("required" => true, "label" => $this->trans("table.diploma.csn"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.diploma.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.diploma.status.1") => 1,
                    $this->trans("table.diploma.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("type" => "employee_diploma", "csn" => $entity->getCsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getCsn()));
                } else {
                    $entity->setType("employee_diploma");
                    $entity->setPid(0);
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("diploma_list"));
                }
            }
        }

        return $this->render("admin/diploma/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $diplomaId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($diplomaId);
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
                $diploma = json_decode($this->getRequest()->getContent(), true);
                $id = $diploma['id'];
                unset($diploma['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $diploma = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($diploma['queue']);
            $entity->setCsn($diploma['csn']);
            $entity->setStatus($diploma['status']);
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
                return $this->redirect($this->generateUrl("diploma_list"));
            }
        }
    }

    private function createDiplomaFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $diplomas = array_slice($this->getCache("UserBundle:Employee_Diploma"), ($page - 1) * $offset, $offset);

        if (count($diplomas) > 0) {
            foreach ($diplomas as $diploma) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $diploma['id']))
                    ->add("queue", NumberType::class, array("required" => true, "data" => $diploma['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("csn", TextType::class, array("required" => true, "data" => $diploma['csn'], "label" => $this->trans($diploma['csn']), "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.diploma.status.1") => 1,
                            $this->trans("table.diploma.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $diploma['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    private function getDiplomaForChoices()
    {
        $diplomaChoices = array("0" => $this->trans("common.choose"));
        $diplomas = $this->getCache("UserBundle:Employee_Diploma");
        if (count($diplomas) > 0) {
            foreach ($diplomas as $diploma) {
                if ($diploma["pid"] == 0) {
                    $diplomaChoices[$diploma['id']] = $this->trans($diploma['csn']);
                }
            }
        }

        return $diplomaChoices;
    }

    final public function dumpCache()
    {
        // for diploma
        $diplomas = array();
        $diplomaEntity = $this->getRepo("UserBundle:Category")->getAll("employee_diploma");
        if (count($diplomaEntity) > 0) {
            foreach ($diplomaEntity as $diploma) {
                $diplomas[$diploma['id']] = $diploma;
            }
        }
        parent::buildCache($diplomas, "UserBundle:Employee_Diploma");
    }

}
