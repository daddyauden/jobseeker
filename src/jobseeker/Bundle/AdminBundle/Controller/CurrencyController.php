<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class CurrencyController extends AdminBase
{

    protected $entityName = "AdminBundle:Currency";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 2;
        $pager = $this->generatePager("/admin/currency-list-%d", $page, $offset, count($this->getCache()));

        return $this->render('admin/currency/list.html.twig', array('currencys' => $this->createFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function createFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $currencys = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($currencys) > 0) {
            foreach ($currencys as $currency) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $currency['id']))
                    ->add("symbol", TextType::class, array("required" => false, "data" => $currency['symbol'], "attr" => array("class" => "form-control input-sm")))
                    ->add("code", TextType::class, array(
                        "data" => $currency['code'],
                        "label" => $this->trans($currency['code'] . ".title"),
                        "attr" => array(
                            "class" => "form-control input-sm",
                            "title" => $this->trans($currency['code'] . ".title")
                        )
                    ))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => [
                            $this->trans("table.currency.status.1") => 1,
                            $this->trans("table.currency.status.0") => 0
                        ],
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $currency['status']
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
            ->setAction($this->generateUrl("currency_add"))
            ->setMethod("post")
            ->add("symbol", TextType::class, array("required" => false, "label" => $this->trans("table.currency.symbol"), "attr" => array("class" => "form-control input-sm")))
            ->add("code", TextType::class, array("label" => $this->trans("table.currency.code"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.currency.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.currency.status.1") => 1,
                    $this->trans("table.currency.status.0") => 0
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
                    return $this->redirect($this->generateUrl("currency_list"));
                }
            }
        }

        return $this->render("admin/currency/add.html.twig", array("form" => $form->createView()));
    }

    public function saveAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $currency = json_decode($this->getRequest()->getContent(), true);
                $id = $currency['id'];
                unset($currency['id']);
                $entity = $this->getRepo($this->getEntityName())->find($id);
                $oldentity = clone $entity;
            } else {
                $currency = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setSymbol($currency['symbol']);
            $entity->setCode($currency['code']);
            $entity->setStatus($currency['status']);
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
                return $this->redirect($this->generateUrl("currency_list"));
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
                $currencyId = $this->getRequest()->getContent();
                $entity = $this->getRepo($this->getEntityName())->find($currencyId);
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

    final public function dumpCache()
    {
        // for currency
        $currencies = array();
        $currencyEntity = $this->getRepo("AdminBundle:Currency")->getAll();
        if (count($currencyEntity) > 0) {
            foreach ($currencyEntity as $currency) {
                $currencies[$currency['code']] = $currency;
                $currencies[$currency['code']]['alias'] = $this->trans($currency['code'] . ".title");
            }
        }
        parent::buildCache($currencies, "AdminBundle:Currency");
    }

}
