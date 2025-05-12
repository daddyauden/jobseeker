<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class PaymenttypeController extends AdminBase
{

    protected $entityName = "UserBundle:Category";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/paymenttype-list-%d", $page, $offset, count($this->getCache("CareerBundle:Payment_Type")));

        return $this->render("admin/paymenttype/list.html.twig", array("types" => $this->createPaymentTypeFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("paymenttype_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => true, "label" => $this->trans("table.paymenttype.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("csn", TextType::class, array("required" => true, "label" => $this->trans("table.paymenttype.csn"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.paymenttype.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.paymenttype.status.1") => 1,
                    $this->trans("table.paymenttype.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("type" => "payment_type", "csn" => $entity->getCsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getCsn()));
                } else {
                    $entity->setType("payment_type");
                    $entity->setPid(0);
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("paymenttype_list"));
                }
            }
        }

        return $this->render("admin/paymenttype/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $paymenttypeId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($paymenttypeId);
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
                $paymenttype = json_decode($this->getRequest()->getContent(), true);
                $id = $paymenttype['id'];
                unset($paymenttype['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $paymenttype = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($paymenttype['queue']);
            $entity->setCsn($paymenttype['csn']);
            $entity->setStatus($paymenttype['status']);
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
                return $this->redirect($this->generateUrl("paymenttype_list"));
            }
        }
    }

    private function createPaymentTypeFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $paymenttypes = array_slice($this->getCache("CareerBundle:Payment_Type"), ($page - 1) * $offset, $offset);

        if (count($paymenttypes) > 0) {
            foreach ($paymenttypes as $paymenttype) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $paymenttype['id']))
                    ->add("queue", NumberType::class, array("required" => true, "data" => $paymenttype['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("csn", TextType::class, array("required" => true, "data" => $paymenttype['csn'], "label" => $this->trans($paymenttype['csn']), "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.paymenttype.status.1") => 1,
                            $this->trans("table.paymenttype.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $paymenttype['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    private function getDiplomaForChoices()
    {
        $paymenttypeChoices = array("0" => $this->trans("common.choose"));
        $paymenttypes = $this->getCache("CareerBundle:Payment_Type");
        if (count($paymenttypes) > 0) {
            foreach ($paymenttypes as $paymenttype) {
                if ($paymenttype["pid"] == 0) {
                    $paymenttypeChoices[$this->trans($paymenttype['csn'])] = $paymenttype['id'];
                }
            }
        }

        return $paymenttypeChoices;
    }

    final public function dumpCache()
    {
        // for payment type
        $paymenttyies = array();
        $paymenttypeEntity = $this->getRepo("UserBundle:Category")->getAll("payment_type");
        if (count($paymenttypeEntity) > 0) {
            foreach ($paymenttypeEntity as $paymenttype) {
                $paymenttyies[$paymenttype['id']] = $paymenttype;
            }
        }
        parent::buildCache($paymenttyies, "CareerBundle:Payment_Type");
    }

}
