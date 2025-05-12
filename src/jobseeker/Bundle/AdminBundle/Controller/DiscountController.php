<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class DiscountController extends AdminBase
{

    protected $entityName = "CareerBundle:Discount";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/discount-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/discount/list.html.twig", array("discounts" => $this->createFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function createFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $discounts = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($discounts) > 0) {
            foreach ($discounts as $discount) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $discount['id']))
                    ->add("queue", NumberType::class, array("required" => false, "data" => $discount['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("rate", NumberType::class, array("data" => $discount['rate'], "attr" => array("class" => "form-control input-sm")))
                    ->add("dsn", TextType::class, array("data" => $discount['dsn'], "label" => $this->trans($discount['dsn']), "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.discount.status.1") => 1,
                            $this->trans("table.discount.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $discount['status']
                    ))
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
            ->setAction($this->generateUrl("discount_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => false, "label" => $this->trans("table.discount.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("dsn", TextType::class, array("label" => $this->trans("table.discount.dsn"), "attr" => array("class" => "form-control input-sm")))
            ->add("rate", NumberType::class, array("label" => $this->trans("table.discount.rate"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.discount.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.discount.status.1") => 1,
                    $this->trans("table.discount.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("dsn" => $entity->getDsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getDsn()));
                } else {
                    if (1 < $rate = floatval($entity->getRate())) {
                        $rate = round(min(array($rate, 100)) / 100, 2, PHP_ROUND_HALF_UP);
                    } else {
                        $rate = round($rate, 2, PHP_ROUND_HALF_UP);
                    }
                    $entity->setRate($rate);
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("discount_list"));
                }
            }
        }

        return $this->render("admin/discount/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $discountId = $this->getRequest()->getContent();
                $entity = $this->getRepo($this->getEntityName())->find($discountId);
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
                $discount = json_decode($this->getRequest()->getContent(), true);
                $id = $discount['id'];
                unset($discount['id']);
                $entity = $this->getRepo($this->getEntityName())->find($id);
                $oldentity = clone $entity;
            } else {
                $discount = $this->getFormData();
                $entity = $this->getEntity();
            }
            if (1 < $rate = floatval($discount['rate'])) {
                $rate = round(min(array($rate, 100)) / 100, 2, PHP_ROUND_HALF_UP);
            } else {
                $rate = round($rate, 2, PHP_ROUND_HALF_UP);
            }
            $entity->setRate($rate);
            $entity->setDsn($discount['dsn']);
            $entity->setQueue($discount['queue']);
            $entity->setStatus($discount['status']);
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
                return $this->redirect($this->generateUrl("discount_list"));
            }
        }
    }

    final public function dumpCache()
    {
        // for discount
        $discounts = array();
        $discountEntity = $this->getRepo("CareerBundle:Discount")->getAll();
        if (count($discountEntity) > 0) {
            foreach ($discountEntity as $discount) {
                $discounts[$discount['id']] = $discount;
                $discounts[$discount['id']]['alias'] = $this->trans(strtoupper($discount['dsn']));
            }
        }

        parent::buildCache($discounts, "CareerBundle:Discount");
    }

}
