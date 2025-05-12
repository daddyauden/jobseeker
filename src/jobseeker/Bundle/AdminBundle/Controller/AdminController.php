<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class AdminController extends AdminBase
{

    protected $entityName = "AdminBundle:Admin";

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }
        $offset = $this->getPerPage();
        $pager = $this->generatePager("/admin/admin-list-%d", $page, $offset, count($this->getCache()));

        return $this->render("admin/admin/list.html.twig", array("admins" => $this->createFormWithValue($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function createFormWithValue($page = 1, $offset = 10)
    {
        $forms = array();
        $datas = array_slice($this->getCache(), ($page - 1) * $offset, $offset);
        if (count($datas) > 0) {
            foreach ($datas as $data) {
                $forms[] = $this->createFormBuilder($this->getEntity(), array("attr" => array("role" => "form", "class" => "form")))
                    ->add("id", HiddenType::class, array("data" => $data['id']))
                    ->add("rid", TextType::class, array("label" => $this->trans('role.' . $data['role']), "attr" => array("class" => "form-control")))
                    ->add('email', EmailType::class, array("label" => $data['email'], "attr" => array("class" => "form-control")))
                    ->add("logintime", TextType::class, array("label" => $data['logintime'], "attr" => array("class" => "form-control")))
                    ->add("loginip", TextType::class, array("label" => $data['loginip'], "attr" => array("class" => "form-control")))
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
            ->setAction($this->generateUrl("admin_add"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("label" => $this->trans("table.admin.email"), "attr" => array("class" => "form-control")))
            ->add("password", PasswordType::class, array("label" => $this->trans("table.admin.password"), "attr" => array("class" => "form-control")))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                if (null !== $this->getRepo()->findOneBy(array("email" => $entity->getEmail()))) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s is exist, Please Change Another One", $entity->getEmail()));
                } else {
                    $role = $this->get("RoleManager")->findRoleByName("admin");
                    if ($role) {
                        $entity->setRid($role);
                    } else {
                        $entity->setRid($this->get("RoleManager")->findRoleByName());
                    }
                    $entity->setLoginIp($this->getClientIp());
                    $entity->setLogintime(time());
                    $this->getEm()->persist($entity);
                    $this->getEm()->flush();
                    $this->dumpCache();
                    return $this->redirect($this->generateUrl("admin_list"));
                }
            }
        }

        return $this->render('admin/admin/add.html.twig', array("form" => $form->createView()));
    }

    public function changepassAction($aid)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $admin = $this->getRepo()->find($aid);

        $form = $this->createFormBuilder($admin, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("admin_save"))
            ->setMethod("post")
            ->add("id", HiddenType::class, array("data" => $admin->getId()))
            ->add("password", PasswordType::class, array("label" => $this->trans("table.admin.newpassword"), "attr" => array("class" => "form-control")))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                $this->getEm()->persist($admin);
                $this->getEm()->flush();
                $this->get("session")->getFlashBag()->add("info", $this->trans("alert.success"));
                return $this->redirect($this->generateUrl("admin_list", array("page" => 1)));
            }
        }

        return $this->render('admin/admin/changepass.html.twig', array("form" => $form->createView()));
    }

    public function changeemailAction($aid)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $admin = $this->getRepo()->find($aid);

        $form = $this->createFormBuilder($admin, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("admin_save"))
            ->setMethod("post")
            ->add("id", HiddenType::class, array("data" => $admin->getId()))
            ->add('email', EmailType::class, array("label" => $this->trans("table.admin.newemail"), "attr" => array("class" => "form-control", "placeholder" => $admin->getEmail())))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        return $this->render('admin/admin/changeemail.html.twig', array("form" => $form->createView()));
    }

    public function changeroleAction($aid)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $admin = $this->getRepo()->find($aid);

        $roles = $this->getCache("UserBundle:Role");

        $choices = [
            $this->trans("common.choose") => ""
        ];

        if (count($roles) > 1) {
            foreach ($roles as $role) {
                $choices[$this->trans("role." . $role['name'])] = $role['id'];
            }
        }

        $form = $this->createFormBuilder($admin, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("admin_save"))
            ->setMethod("post")
            ->add("id", HiddenType::class, array("data" => $admin->getId()))
            ->add("rid", ChoiceType::class, array(
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "label" => $this->trans("table.admin.newrid"),
                "choices" => $choices,
                "multiple" => false,
                'required' => true,
                'expanded' => false,
                "data" => $admin->getRid()->getId()
            ))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        return $this->render('admin/admin/changerole.html.twig', array("form" => $form->createView()));
    }

    public function saveAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $admin = json_decode($this->getRequest()->getContent(), true);
                $id = $admin['id'];
                unset($admin['id']);
                $entity = $this->getRepo()->find($id);
                $oldentity = clone $entity;
            } else {
                $admin = $this->getFormData();
                if (isset($admin['id'])) {
                    $entity = $this->getRepo()->find($admin['id']);
                } else {
                    $entity = $this->getEntity();
                }
            }

            if (isset($admin['email'])) {
                $entity->setEmail($admin['email']);
            }

            if (isset($admin['password'])) {
                $entity->setPassword($admin['password']);
            }

            if (isset($admin['rid'])) {
                $newRid = $this->get("RoleManager")->findRoleById($admin['rid']);
                if ($newRid !== $entity->getRid()) {
                    $entity->setRid($newRid);
                }
            }

            if ($this->isAjax()) {
                if (isset($oldentity) && $oldentity == $entity) {
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
                return $this->redirect($this->generateUrl("admin_list"));
            }
        }
    }

    final public function dumpCache()
    {
        //for admin user
        $admins = array();
        $adminEntity = $this->getRepo("AdminBundle:Admin")->getAll();
        if (count($adminEntity) > 0) {
            foreach ($adminEntity as $admin) {
                $admins[$admin['email']] = array(
                    'id' => $admin['id'],
                    "email" => $admin['email'],
                    'loginip' => $admin['loginip'],
                    'role' => $admin['rid']['name'],
                    'logintime' => date("Y:m:d H:i:s e", $admin['logintime'])
                );
            }
        }
        parent::buildCache($admins, "AdminBundle:Admin");
    }

}
