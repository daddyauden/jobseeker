<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class EmployerController extends AdminBase
{

    protected $entityName = "UserBundle:Employer";

    public function searchAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $form = $this->createFormBuilder(null, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("employer_search"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("label" => $this->trans("table.user.email"), "attr" => array("class" => "form-control input-sm")))
            ->add("submit", SubmitType::class, array("label" => $this->trans("be.submit"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("submit")->isClicked()) {
                $content = $this->getFormData();
                $employers = $this->getRepo()->getByUser(array("email", $content['email']));
                if ($employers && $employer = $employers[0]) {
                    $uid = $employer["uid"]["id"];
                    return $this->render("admin/employer/search.html.twig", array(
                        "form" => $form->createView(),
                        "employer" => $employer
                    ));
                } else {
                    $this->get("session")->getFlashBag()->add("danger", "User Exists, Please Change Another Email");
                    return $this->render('admin/employer/search.html.twig', array("form" => $form->createView()));
                }
            }
        }

        return $this->render('admin/employer/search.html.twig', array("form" => $form->createView()));
    }

    public function addAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $user = $this->getEntity("UserBundle:User");

        $role = $this->get("RoleManager")->findRoleByName();

        if ($role) {
            $user->setRid($role);
        } else {
            $user->setRid($this->get("RoleManager")->findRoleByName());
        }

        $form = $this->createFormBuilder($user, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("employer_add"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("label" => $this->trans("table.user.email"), "attr" => array("class" => "form-control input-sm")))
            ->add("password", PasswordType::class, array("label" => $this->trans("table.user.password"), "attr" => array("class" => "form-control input-sm")))
            ->add("save", SubmitType::class, array("label" => $this->trans("be.save"), "attr" => array("class" => "btn btn-lg btn-primary btn-block")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                $email = $user->getEmail();
                if ($this->getRepo("UserBundle:User")->findOneBy(array("email" => $email))) {
                    $this->get("session")->getFlashBag()->add("danger", "User Exists, Please Change Another Email");
                } else {
                    $user->setLogintime(time());
                    $this->getEm()->persist($user);
                    $this->getEm()->flush();
                    return $this->redirect($this->generateUrl("employer_add"));
                }
            }
        }

        return $this->render('admin/employer/add.html.twig', array("form" => $form->createView()));
    }

    public function listAction($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $offset = $this->getPerPage() * 2;
        $pager = $this->generatePager("/admin/employer-list-%d", $page, $offset, $this->getRepo()->getTotal());

        return $this->render("admin/employer/list.html.twig", array("employers" => $this->getEmployer($page, $offset), "urls" => $pager->generateUrl()));
    }

    private function getEmployer($page = 1, $offset = 10)
    {
        $data = $this->getRepo()->getAllForPager($page, $offset);

        return count($data) > 0 ? $data : array();
    }

}
