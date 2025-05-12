<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class TypeController extends AdminBase
{

    protected $entityName = "CareerBundle:Type";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/type-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/type/list.html.twig", array("types" => $this->createFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $entity = $this->getEntity();

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("type_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => true, "label" => $this->trans("table.type.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("tsn", TextType::class, array("required" => true, "label" => $this->trans("table.type.tsn"), "attr" => array("class" => "form-control input-sm")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.type.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.type.status.1") => 1,
                    $this->trans("table.type.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("tsn" => $entity->getTsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getTsn()));
                } else {
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    $this->saveTypeToRedis($entity);
                    return $this->redirect($this->generateUrl("type_list"));
                }
            }
        }

        return $this->render("admin/type/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $typeId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($typeId);
                try {
                    $this->saveTypeToRedis($entity, "del");
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
                $type = json_decode($this->getRequest()->getContent(), true);
                $id = $type['id'];
                unset($type['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $type = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($type['queue']);
            $entity->setTsn($type['tsn']);
            $entity->setStatus($type['status']);
            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->flush();
                        $this->dumpCache();
                        $this->saveTypeToRedis($entity);
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
            } else {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                $this->saveTypeToRedis($entity);
                return $this->redirect($this->generateUrl("type_list"));
            }
        }
    }

    private function createFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $types = array_slice($this->getCache(), ($page - 1) * $offset, $offset);

        if (count($types) > 0) {
            foreach ($types as $type) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $type['id']))
                    ->add("queue", NumberType::class, array("required" => true, "data" => $type['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("tsn", TextType::class, array("required" => true, "data" => $type['tsn'], "label" => $this->trans($type['tsn']), "attr" => array("class" => "form-control input-sm")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.type.status.1") => 1,
                            $this->trans("table.type.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $type['status']
                    ))
                    ->getForm()->createView();
            }
        }
        return $forms;
    }

//    private function getTypeForChoices()
//    {
//        $typeChoices = array("0" => $this->trans("common.choose"));
//        $types = $this->getCache();
//        if (count($types) > 0) {
//            foreach ($types as $type) {
//                $typeChoices[$this->trans($type['tsn'])] = $type['id'];
//            }
//        }
//        return $typeChoices;
//    }

    final public function dumpCache()
    {
        // for type
        $types = array();
        $typeEntity = $this->getRepo("CareerBundle:Type")->getAll();
        if (count($typeEntity) > 0) {
            foreach ($typeEntity as $type) {
                $types[$type['id']] = $type;
            }
        }
        parent::buildCache($types, "CareerBundle:Type");
    }

}
