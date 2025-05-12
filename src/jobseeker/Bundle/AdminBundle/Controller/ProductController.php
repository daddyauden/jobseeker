<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class ProductController extends AdminBase
{

    protected $entityName = "CareerBundle:Product";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/product-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/product/list.html.twig", array("currency" => $this->trans($this->getSystem("currency") . ".title"), "products" => $this->createProductFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("product_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => false, "label" => $this->trans("table.product.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("duration", NumberType::class, array("label" => $this->trans("table.product.duration") . "(" . $this->trans("common.date.day") . ")", "attr" => array("class" => "form-control input-sm")))
            ->add("price", NumberType::class, array("label" => $this->trans("table.product.price") . "(" . $this->trans($this->getSystem("currency") . ".title") . ")", "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.product.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.product.status.1") => 1,
                    $this->trans("table.product.status.0") => 0
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
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                $this->saveProductToRedis($entity);
                return $this->redirect($this->generateUrl("product_list"));
            }
        }

        return $this->render("admin/product/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $productId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($productId);
                try {
                    $this->saveProductToRedis($entity, "del");
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
                $product = json_decode($this->getRequest()->getContent(), true);
                $id = $product['id'];
                unset($product['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $product = $this->getFormData();
                $entity = $this->getEntity();
            }

            $entity->setDuration($product['duration']);
            $entity->setPrice($product['price']);
            $entity->setQueue($product['queue']);
            $entity->setStatus($product['status']);
            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->persist($entity);
                        $this->getEm()->flush();
                        $this->dumpCache();
                        $this->saveProductToRedis($entity);
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
            } else {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                $this->saveProductToRedis($entity);
                return $this->redirect($this->generateUrl("product_list"));
            }
        }
    }

    private function createProductFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $products = array_slice($this->getCache(), ($page - 1) * $offset, $offset);

        if (count($products) > 0) {
            foreach ($products as $product) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $product['id']))
                    ->add("queue", NumberType::class, array("required" => false, "data" => $product['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("duration", NumberType::class, array("data" => $product['duration'] / (3600 * 24), "attr" => array("class" => "form-control input-sm")))
                    ->add("price", NumberType::class, array("data" => $product['price'], "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.product.status.1") => 1,
                            $this->trans("table.product.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $product['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    final public function dumpCache()
    {
        // for product
        $products = array();
        $productEntity = $this->getRepo("CareerBundle:Product")->getAll();
        if (count($productEntity) > 0) {
            foreach ($productEntity as $product) {
                $products[$product['id']] = $product;
            }
        }
        parent::buildCache($products, "CareerBundle:Product");
    }

}
