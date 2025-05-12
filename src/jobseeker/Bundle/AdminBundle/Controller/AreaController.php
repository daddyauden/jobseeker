<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class AreaController extends AdminBase
{

    protected $entityName = "AdminBundle:Area";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 3;
        $pager = $this->generatePager("/admin/area-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/area/list.html.twig", array("areas" => $this->createFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function createFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $areas = array_slice($this->getCache(), ($page - 1) * $offset, $offset);

        if (count($areas) > 0) {
            foreach ($areas as $area) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $area['id']))
                    ->add("queue", NumberType::class, array("required" => false, "data" => $area['queue'], "attr" => array("class" => "form-control input-sm")))
                    ->add("level", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.area.level.1") => 1,
                            $this->trans("table.area.level.2") => 2,
                            $this->trans("table.area.level.3") => 3,
                            $this->trans("table.area.level.4") => 4
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $area['level']
                    ))
                    ->add("pid", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => $this->getPidForChoices($area['level']),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $area['pid']
                    ))
                    ->add("code", TextType::class, array(
                        "data" => $area['code'],
                        "label" => $this->trans(strtoupper($area['code'])),
                        "attr" => array(
                            "class" => "form-control input-sm"
                        )
                    ))
                    ->add("alpha", TextType::class, array("data" => $area['alpha'], "attr" => array("class" => "form-control")))
                    ->add("domain", TextType::class, array("required" => false, "data" => $area['domain'], "attr" => array("class" => "form-control")))
                    ->add("lat", TextType::class, array("required" => false, "data" => $area['lat'], "attr" => array("class" => "form-control")))
                    ->add("lng", TextType::class, array("required" => false, "data" => $area['lng'], "attr" => array("class" => "form-control")))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.area.status.1") => 1,
                            $this->trans("table.area.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $area['status']
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
            ->setAction($this->generateUrl("area_add"))
            ->setMethod("post")
            ->add("queue", NumberType::class, array("required" => false, "label" => $this->trans("table.area.queue"), "attr" => array("class" => "form-control input-sm")))
            ->add("level", ChoiceType::class, array(
                "label" => $this->trans("table.area.level"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.area.level.1") => 1,
                    $this->trans("table.area.level.2") => 2,
                    $this->trans("table.area.level.3") => 3,
                    $this->trans("table.area.level.4") => 4
                ),
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                "data" => 2
            ))
            ->add("pid", ChoiceType::class, array(
                "label" => $this->trans("table.area.pid"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getPidForChoices(),
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                "data" => 0
            ))
            ->add("code", TextType::class, array("label" => $this->trans("table.area.code"), "attr" => array("class" => "form-control")))
            ->add("alpha", TextType::class, array("label" => $this->trans("table.area.alpha"), "attr" => array("class" => "form-control")))
            ->add("domain", TextType::class, array("required" => false, "label" => $this->trans("table.area.domain"), "attr" => array("class" => "form-control")))
            ->add("lat", TextType::class, array("required" => false, "label" => $this->trans("table.area.lat"), "attr" => array("class" => "form-control")))
            ->add("lng", TextType::class, array("required" => false, "label" => $this->trans("table.area.lng"), "attr" => array("class" => "form-control")))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.area.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.area.status.1") => 1,
                    $this->trans("table.area.status.0") => 0
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
                    $this->saveAreaToRedis($entity);
                    return $this->redirect($this->generateUrl("area_list"));
                }
            }
        }

        return $this->render("admin/area/add.html.twig", array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $areaId = $this->getRequest()->getContent();
                $entity = $this->getRepo()->find($areaId);
                try {
                    $this->saveAreaToRedis($entity, "del");
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
                $area = json_decode($this->getRequest()->getContent(), true);
                $id = $area['id'];
                unset($area['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $area = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setQueue($area['queue']);
            $entity->setLevel($area['level']);
            $entity->setPid($area['pid']);
            $entity->setCode(strtolower($area['code']));
            $entity->setAlpha(strtolower($area['alpha']));
            $entity->setDomain(strtolower($area['domain']));
            $entity->setLat($area['lat']);
            $entity->setLng($area['lng']);
            $entity->setStatus($area['status']);
            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->persist($entity);
                        $this->getEm()->flush();
                        $this->dumpCache();
                        $this->saveAreaToRedis($entity);
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
            } else {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                $this->saveAreaToRedis($entity);
                return $this->redirect($this->generateUrl("area_list"));
            }
        }
    }

    private function getPidForChoices($level = NULL)
    {
        $area = array($this->trans("common.choose") => 0);
        $result = $this->getCache();
        if (count($result) > 0) {
            if ($level)

                if (1 < $level) {
                    --$level;
                    foreach ($result as $value) {
                        if ($level == $value['level']) {
                            $area[$this->trans(strtoupper($value['code']))] = $value['id'];
                        }
                    }
                }

            if (NULL === $level) {
                foreach ($result as $value) {
                    $level = (int)$value['level'];
                    if ($level > 3) {
                        $mark = str_repeat("-", 3);

                    } elseif ($level > 2) {
                        $mark = str_repeat("-", 2);
                    } else if ($level > 1) {
                        $mark = str_repeat("-", 1);
                    } else {

                    }
                    $area["--" . $this->trans(strtoupper($value['code']))] = $value['id'];
                }
            }
        }

        return $area;
    }

    final public function dumpCache()
    {
        // for area
        $areas = array();
        $areaEntity = $this->getRepo("AdminBundle:Area")->getAll();
        if (count($areaEntity) > 0) {
            foreach ($areaEntity as $area) {
                $areas[$area['id']] = $area;
                $areas[$area['id']]['alias'] = $this->trans(strtoupper($area['code']));
                if ($area['level'] != 1) {
                    $areas[$area['pid']]['child'][$area['id']] = $areas[$area['id']];
                }
            }
        }
        foreach ($areas as $areaid => $area) {
            if (array_key_exists("child", $area) && !array_key_exists("id", $area)) {
                unset($areas[$areaid]);
            }
        }
        parent::buildCache($areas, "AdminBundle:Area");
    }

}
