<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class EmployertypeController extends AdminBase
{

    protected $entityName = "UserBundle:Category";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/employertype-list-%d", $page, $offset, count($this->getCache("UserBundle:Employer_Type")));

        return $this->render("admin/employertype/list.html.twig", array("types" => $this->createEmployerTypeFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("employertype_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => true, "label" => $this->trans("table.employertype.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("csn", TextType::class, array("required" => true, "label" => $this->trans("table.employertype.csn"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.employertype.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.employertype.status.1") => 1,
                    $this->trans("table.employertype.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("type" => "employer_type", "csn" => $entity->getCsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getCsn()));
                } else {
                    $entity->setType("employer_type");
                    $entity->setPid(0);
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("employertype_list"));
                }
            }
        }

        return $this->render("admin/employertype/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $employertypeId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($employertypeId);
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
                $employertype = json_decode($this->getRequest()->getContent(), true);
                $id = $employertype['id'];
                unset($employertype['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $employertype = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($employertype['queue']);
            $entity->setCsn($employertype['csn']);
            $entity->setStatus($employertype['status']);
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
                return $this->redirect($this->generateUrl("employertype_list"));
            }
        }
    }

    private function createEmployerTypeFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $employertypes = array_slice($this->getCache("UserBundle:Employer_Type"), ($page - 1) * $offset, $offset);

        if (count($employertypes) > 0) {
            foreach ($employertypes as $employertype) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $employertype['id']))
                    ->add("queue", NumberType::class, array("required" => true, "data" => $employertype['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("csn", TextType::class, array("required" => true, "data" => $employertype['csn'], "label" => $this->trans($employertype['csn']), "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.employertype.status.1") => 1,
                            $this->trans("table.employertype.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $employertype['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    final public function dumpCache()
    {
        // for employer type
        $employertypes = array();
        $employertypeEntity = $this->getRepo("UserBundle:Category")->getAll("employer_type");
        if (count($employertypeEntity) > 0) {
            foreach ($employertypeEntity as $employertype) {
                $employertypes[$employertype['id']] = $employertype;
            }
        }
        parent::buildCache($employertypes, "UserBundle:Employer_Type");
    }

}
