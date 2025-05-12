<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class EmployerscaleController extends AdminBase
{

    protected $entityName = "UserBundle:Category";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/employerscale-list-%d", $page, $offset, count($this->getCache("UserBundle:Employer_Scale")));

        return $this->render("admin/employerscale/list.html.twig", array("scales" => $this->createEmployerscaleFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("employerscale_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => true, "label" => $this->trans("table.employerscale.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("csn", TextType::class, array("required" => true, "label" => $this->trans("table.employerscale.csn"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.employerscale.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.employerscale.status.1") => 1,
                    $this->trans("table.employerscale.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("type" => "employer_scale", "csn" => $entity->getCsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getCsn()));
                } else {
                    $entity->setType("employer_scale");
                    $entity->setPid(0);
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("employerscale_list"));
                }
            }
        }

        return $this->render("admin/employerscale/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $scaleId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($scaleId);
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
                $employerscale = json_decode($this->getRequest()->getContent(), true);
                $id = $employerscale['id'];
                unset($employerscale['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $employerscale = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($employerscale['queue']);
            $entity->setCsn($employerscale['csn']);
            $entity->setStatus($employerscale['status']);
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
                return $this->redirect($this->generateUrl("employerscale_list"));
            }
        }
    }

    private function createEmployerscaleFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $employerscales = array_slice($this->getCache("UserBundle:Employer_Scale"), ($page - 1) * $offset, $offset);

        if (count($employerscales) > 0) {
            foreach ($employerscales as $employerscale) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $employerscale['id']))
                    ->add("queue", NumberType::class, array("required" => true, "data" => $employerscale['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("csn", TextType::class, array("required" => true, "data" => $employerscale['csn'], "label" => $this->trans($employerscale['csn']), "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.employerscale.status.1") => 1,
                            $this->trans("table.employerscale.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $employerscale['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    final public function dumpCache()
    {
        // for employer scale
        $employerscales = array();
        $employerscaleEntity = $this->getRepo("UserBundle:Category")->getAll("employer_scale");
        if (count($employerscaleEntity) > 0) {
            foreach ($employerscaleEntity as $employerscale) {
                $employerscales[$employerscale['id']] = $employerscale;
            }
        }
        parent::buildCache($employerscales, "UserBundle:Employer_Scale");
    }

}
