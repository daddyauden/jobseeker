<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class RoleController extends AdminBase
{

    protected $entityName = "UserBundle:Role";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/role-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/role/list.html.twig", array("roles" => $this->createRoleFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    public function showAction($rid)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $data = array();
        $role = $this->findRoleBy('id', $rid);
        $privileges = $this->getCache("AdminBundle:Privilege");

        if (!$privileges) {
            $privileges = $this->getRepo("AdminBundle:Privilege")->getAll();
        }

        foreach ($privileges as $privilege) {
            $data[strtolower($privilege["bundle"])][strtolower($privilege["bundle"] . "." . $privilege['controller'])][$privilege["id"]] = $privilege["route"];
        }

        return $this->render('admin/role/show.html.twig', array('role' => $role, "privileges" => $data));
    }

    private function findRoleBy($key, $value)
    {
        if (!empty($value)) {
            $role = $this->get('RoleManager')->{'findRoleBy' . ucfirst($key)}($value);
        }

        if (empty($role)) {
            throw new \Exception(sprintf('The role with "%s" does not exist for value "%s"', $key, $value));
        }

        return $role;
    }

    public function saveAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $role = json_decode($this->getRequest()->getContent(), true);
                $id = $role['id'];
                unset($role['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $role = $this->getFormData();
                if (isset($role['id'])) {
                    $entity = $this->getRepo()->find($role['id']);
                } else {
                    $entity = $this->getEntity();
                }
            }

            if (isset($role['name']) && !isset($role['pid'])) {
                $entity->setName($role['name']);
            }

            if (isset($role['pid']) && !isset($role['name'])) {
                $entity->setPid($role['pid']);
            }

            if (!isset($role['pid']) && !isset($role['name'])) {
                $entity->setPid(array());
            }

            if ($this->isAjax()) {
                if ($oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->persist($entity);
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
                return $this->redirect($this->generateUrl("role_list"));
            }
        }
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $roleManager = $this->get('RoleManager');
        $role = $roleManager->createRole();

        $form = $this->createFormBuilder($role, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("role_add"))
            ->setMethod("post")
            ->add('name', TextType::class, array("data" => "", "label" => $this->trans("table.role.name"), "attr" => array("class" => "form-control input-sm")))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                try {
                    $roleManager->updateRole($role);
                } catch (\Exception $e) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $role->getName()));
                    return $this->render('admin/role/add.html.twig', array("form" => $form->createView()));
                }
                $this->dumpCache();
                return $this->redirect($this->generateUrl("role_list"));
            }
        }

        return $this->render('admin/role/add.html.twig', array("form" => $form->createView()));
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $rid = $this->getRequest()->getContent();
                $role = $this->findRoleBy('id', $rid);
                $this->get("RoleManager")->deleteRole($role);
                $this->dumpCache();
                return $this->render("empty.html.twig", array("status" => "success"));
            }
        }
    }

    private function createRoleFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $roles = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($roles) > 0) {
            foreach ($roles as $role) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $role['id']))
                    ->add("name", TextType::class, array(
                        "label" => $this->trans('role.' . $role['name']),
                        "data" => $role['name'],
                        "attr" => array("class" => "form-control input-sm")
                    ))->getForm()->createView();
            }
        }

        return $forms;
    }

    final public function dumpCache()
    {
        // for admin role
        $roles = array();
        $roleEntity = $this->getRepo("UserBundle:Role")->getAll();
        if (count($roleEntity) > 0) {
            foreach ($roleEntity as $role) {
                $roles[$role['id']] = $role;
                $roles[$role['id']]['alias'] = $this->trans('role.' . $role['name']);
            }
        }
        parent::buildCache($roles, "UserBundle:Role");
    }

}
