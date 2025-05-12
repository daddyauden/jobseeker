<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class PrivilegeController extends AdminBase
{

    protected $entityName = "AdminBundle:Privilege";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 2;
        $pager = $this->generatePager("/admin/privilege-list-%d", $page, $offset, count($this->getCache()));

        return $this->render('admin/privilege/list.html.twig', array("data" => json_encode($this->createPrivileges()), "privileges" => $this->createPrivilegeFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $form = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("privilege_save"))
            ->setMethod("post")
            ->add("bundle", ChoiceType::class, array(
                "label" => $this->trans("table.privilege.bundle"),
                "attr" => array("class" => "form-control input-sm"),
                "required" => true,
                "choices" => array(),
                "multiple" => false,
                'expanded' => false,
            ))
            ->add("controller", ChoiceType::class, array(
                "label" => $this->trans("table.privilege.controller"),
                "attr" => array("class" => "form-control input-sm"),
                "required" => true,
                "choices" => array(),
                "multiple" => false,
                'expanded' => false,
            ))
            ->add("action", ChoiceType::class, array(
                "label" => $this->trans("table.privilege.action"),
                "attr" => array("class" => "form-control input-sm"),
                "required" => true,
                "choices" => array(),
                "multiple" => false,
                'expanded' => false,
            ))
            ->add("status", ChoiceType::class, array(
                "label" => $this->trans("table.privilege.status"),
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    $this->trans("table.privilege.status.1") => 1,
                    $this->trans("table.privilege.status.0") => 0
                ),
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                'data' => 1
            ))
            ->add("save", SubmitType::class, array("attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        return $this->render("admin/privilege/add.html.twig", array("form" => $form->createView(), "data" => json_encode($this->createPrivileges())));
    }

    private function createPrivilegeFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $datas = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($datas)) {
            foreach ($datas as $data) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $data['id']))
                    ->add("bundle", ChoiceType::class, array(
                        "label" => $this->trans($data['route']),
                        "attr" => array(
                            "class" => "form-control input-sm form-bundle"
                        ),
                        "choices" => $this->getPrivilegeForBundle(),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $data['bundle']
                    ))
                    ->add("controller", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm form-controller"
                        ),
                        "choices" => $this->getPrivilegeForController($data['bundle']),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $data['controller']
                    ))
                    ->add("action", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm form-action"
                        ),
                        "choices" => $this->getPrivilegeForAction($data['bundle'], $data['controller']),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        "data" => $data['action']
                    ))
                    ->add("status", ChoiceType::class, array(
                        "attr" => array(
                            "class" => "form-control input-sm"
                        ),
                        "choices" => array(
                            $this->trans("table.privilege.status.1") => 1,
                            $this->trans("table.privilege.status.0") => 0
                        ),
                        "multiple" => false,
                        'required' => true,
                        'expanded' => false,
                        'data' => $data['status']
                    ))
                    ->getForm()->createView();
            }
        }

        return $forms;
    }

    private function getPrivilegeForBundle($column = "bundle")
    {
        $privilege = [$this->trans("common.choose") => ""];
        $datas = $this->getCache("AdminBundle:Privilege_Dump");
        if (count($datas) > 0) {
            foreach ($datas as $bundleName => $data) {
                $privilege[$this->trans(strtolower($bundleName))] = $bundleName;
            }
        }

        return $privilege;
    }

    private function getPrivilegeForController($bundleName)
    {
        $privilege = [$this->trans("common.choose") => ""];
        $datas = $this->getCache("AdminBundle:Privilege_Dump");
        $controllers = $datas[$bundleName]["controller"];
        if (count($controllers) > 0) {
            foreach ($controllers as $controllerName => $controller) {
                $privilege[$this->trans(strtolower($bundleName . "." . $controllerName))] = $controllerName;
            }
        }

        return $privilege;
    }

    private function getPrivilegeForAction($bundleName, $controllerName)
    {
        $privilege = [$this->trans("common.choose") => ""];
        $datas = $this->getCache("AdminBundle:Privilege_Dump");
        $actions = $datas[$bundleName]["controller"][$controllerName]["action"];
        if (count($actions) > 0) {
            foreach ($actions as $actionName => $action) {
                $privilege[$this->trans(strtolower($bundleName . "." . $controllerName . "." . $actionName))] = $actionName;
            }
        }

        return $privilege;
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $privilegeId = $this->getRequest()->getContent();
                $entity = $this->getRepo($this->getEntityName())->find($privilegeId);
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
                $privilege = json_decode($this->getRequest()->getContent(), true);
                $id = $privilege['id'];
                unset($privilege['id']);
                $entity = $this->getRepo($this->getEntityName())->find($id);
                $oldentity = clone $entity;
            } else {
                $privilege = $this->getFormData();
                $entity = $this->getEntity();
            }
            $bundleName = ucwords($privilege['bundle']);
            $controllerName = ucwords($privilege['controller']);
            $actionName = strtolower($privilege['action']);
            $entity->setBundle($bundleName);
            $entity->setController($controllerName);
            $entity->setAction($actionName);
            $entity->setRoute($this->getRouteBaseAction($bundleName, $controllerName, $actionName));
            $entity->setStatus($privilege['status']);
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
                if (null !== $this->getRepo()->findOneBy(array("bundle" => $bundleName, "controller" => $controllerName, "action" => $actionName))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("this privilege is exist!"));
                    return $this->redirect($this->generateUrl("privilege_add"));
                }

                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                return $this->redirect($this->generateUrl("privilege_list"));
            }
        }
    }

    private function createPrivileges()
    {
        return $this->getCache("AdminBundle:Privilege_Dump");
    }

    final public function dumpCache()
    {
        // for privilege
        $privileges = array();
        $privilegeEntity = $this->getRepo("AdminBundle:Privilege")->getAll();
        if (count($privilegeEntity) > 0) {
            foreach ($privilegeEntity as $privilege) {
                $privileges[$privilege['route']] = $privilege;
            }
        }
        parent::buildCache($privileges, "AdminBundle:Privilege");

        $data = array();
        foreach ($this->getBundles() as $bundleName => $bundle) {
            if (null !== $controllers = $this->getControllerInBundle($bundle)) {
                $data[$bundleName]["alias"] = $this->trans(strtolower($bundleName));
                foreach ($controllers as $controllerName => $controllerFullName) {
                    $data[$bundleName]["controller"][$controllerName]["alias"] = $this->trans(strtolower($bundleName . "." . $controllerName));
                    foreach ($this->getActionInController($controllerFullName) as $action) {
                        $data[$bundleName]["controller"][$controllerName]['action'][$action] = $this->trans(strtolower($bundleName . "." . $controllerName . "." . $action));
                    }
                }
            }
        }
        parent::buildCache($data, "AdminBundle:Privilege_Dump");
    }

}
