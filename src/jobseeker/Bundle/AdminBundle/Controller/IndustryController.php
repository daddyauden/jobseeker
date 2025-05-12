<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class IndustryController extends AdminBase
{

    protected $entityName = "CareerBundle:Industry";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 3;
        $pager = $this->generatePager("/admin/industry-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/industry/list.html.twig", array("industrys" => $this->createFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function createFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $industrys = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($industrys) > 0) {
            foreach ($industrys as $industry) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $industry['id']))
                    ->add("queue", NumberType::class, array("required" => true, "data" => $industry['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("isn", TextType::class, array("required" => true, "label" => $this->trans($industry['isn']), "data" => $industry['isn'], "attr" => array("class" => "form-control input-sm")))
                    ->add("pid", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => $this->getIndustryForChoices($industry['id']),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $industry['pid']
                    ))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.industry.status.1") => 1,
                            $this->trans("table.industry.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $industry['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    private function getIndustryForChoices($notIncludeSelf = null, $onlyRoot = false)
    {
        $choices = [$this->trans("common.choose") => 0];
        $industries = $this->getCache();
        if (count($industries) > 0) {
            if (null !== $notIncludeSelf && isset($industries[$notIncludeSelf])) {
                unset($industries[$notIncludeSelf]);
            }
            if (false === $onlyRoot) {
                foreach ($industries as $id => $industry) {
                    if ($industry['pid'] == 0) {
                        $choices[$this->trans($industry['isn'])] = $id;
                        if (isset($industry['child'])) {
                            foreach ($industry['child'] as $childId => $child) {
                                $choices["--" . $this->trans($child['isn'])] = $childId;
                            }
                        }
                    }
                }
            } else {
                foreach ($industries as $id => $industry) {
                    if ($industry['pid'] == 0) {
                        $choices[$this->trans($industry['isn'])] = $id;
                    }
                }
            }
        }

        return $choices;
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $industryEntity = $this->getEntity();

        $form = $this->createFormBuilder($industryEntity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("industry_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => true, "label" => $this->trans("table.industry.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("isn", TextType::class, array("required" => true, "label" => $this->trans("table.industry.isn"), "attr" => array("class" => "form-control input-sm")))
            ->add("pid", ChoiceType::class, array(
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "label" => $this->trans("table.industry.pid"),
                "choices" => $this->getIndustryForChoices(null, true),
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                "data" => 0
            ))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.industry.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.industry.status.1") => 1,
                    $this->trans("table.industry.status.0") => 0
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
                if (null !== $this->getRepo()->findOneBy(array("isn" => $industryEntity->getIsn()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $industryEntity->getIsn()));
                } else {
                    $this->getEm()->persist($industryEntity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    $this->saveIndustryToRedis($industryEntity);
                    return $this->redirect($this->generateUrl("industry_list"));
                }
            }
        }

        return $this->render("admin/industry/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $industryId = $this->getRequest()->getContent();
                $entity = $this->getRepo($this->getEntityName())->find($industryId);
                try {
                    $this->saveIndustryToRedis($entity, "del");
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
                $industry = json_decode($this->getRequest()->getContent(), true);
                $id = $industry['id'];
                unset($industry['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $industry = $this->getFormData();
                $entity = $this->getEntity();
            }

            if ($industry['pid']) {
                if ($industry['pid'] == $entity->getId()) {
                    return $this->render("empty.html.twig", array("status" => "conflicted"));
                }

                $pEntity = $this->getRepo()->find($industry['pid']);
                if ($entity->getPid() && $pEntity->getPid()) {
                    return $this->render("empty.html.twig", array("status" => "samelevel"));
                }
            }

            $entity->setQueue($industry['queue']);
            $entity->setIsn($industry['isn']);
            $entity->setPid($industry['pid']);
            $entity->setStatus($industry['status']);
            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->flush();
                        $this->dumpCache();
                        $this->saveIndustryToRedis($entity);
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
            } else {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                $this->saveIndustryToRedis($entity);
                return $this->redirect($this->generateUrl("industry_list"));
            }
        }
    }

    final public function dumpCache()
    {
        // for industry
        $industries = array();
        $industryEntity = $this->getRepo("CareerBundle:Industry")->getAll();
        if (count($industryEntity) > 0) {
            foreach ($industryEntity as $industry) {
                $industries[$industry['id']] = $industry;
                if ($industry['pid'] != 0) {
                    $industries[$industry['pid']]['child'][$industry['id']] = $industry;
                }
            }
        }

        foreach ($industries as $industryid => $industry) {
            if (array_key_exists("child", $industry) && !array_key_exists("id", $industry)) {
                unset($industries[$industryid]);
            }
        }

        parent::buildCache($industries, "CareerBundle:Industry");
    }

}
