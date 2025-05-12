<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class IndexController extends AdminBase
{

    protected $entityName = "AdminBundle:Admin";

    public function indexAction()
    {
        if (true === parent::autoLogin()) {
            return $this->mainAction();
        } else {
            return $this->loginAction();
        }
    }

    public function loginAction()
    {
        $adminEntity = $this->getEntity();
        $form = $this->createFormBuilder($adminEntity, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("admin_index"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.admin.email"), "autofocus" => '')))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.admin.password"))))
            ->add("submit", SubmitType::class, array("label" => $this->trans("be.submit"), "attr" => array("class" => "btn btn-primary")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("submit")->isClicked()) {
                if (array_key_exists($adminEntity->getEmail(), $this->getCache())) {
                    $admin = $this->getRepo()->findOneBy(array("email" => $adminEntity->getEmail(), "password" => $adminEntity->getPassword()));
                    if ($admin !== null) {
                        $timestamp = time();
                        $admin->setLogintime($timestamp);
                        $admin->setLoginip($this->getClientIp());
                        $this->getEm()->persist($admin);
                        $this->getEm()->flush();
                        $cookie = array(
                            "name" => "auth",
                            "value" => $this->encodeCookie($admin->serialize()),
                            "expire" => $this->printCookieExpire($timestamp),
                            "domain" => $this->getSystem("cookie_domain"),
                            "path" => "/admin",
                            "secure" => false,
                            "httpOnly" => true
                        );
                        $this->sendCookie($cookie);
                        $this->setSession("aid", $admin->getId());
                        return $this->mainAction();
                    } else {
                        $this->get("session")->getFlashBag()->add("danger", "Password Error");
                    }
                } else {
                    $this->get("session")->getFlashBag()->add("danger", "Is Not Admin Account");
                }
            }
        }

        return $this->render("admin/index/login.html.twig", array('form' => $form->createView()));
    }

    public function mainAction()
    {
        // init db system to redis
        $this->setSystem();

        if ($this->hasSession("aid")) {
            $aid = $this->getSession("aid");
        } else if ($this->hasCookie("AUTH")) {
            $cookie = $this->decodeCookie($this->getCookie("AUTH"));
            $aid = $cookie['id'];
            $this->setSession("aid", $aid);
        } else {
            $this->get("session")->getFlashBag()->add("danger", "Invalid Admin User");
            return $this->loginAction();
        }

        return $this->render("admin/index/main.html.twig", array(
            "url" => $this->generateUrl("admin_logout"),
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "admin" => $this->getRepo()->find($aid)
        ));
    }

    public function logoutAction()
    {
        $this->removeSession("aid");
        $cookie = array(
            "name" => "AUTH",
            "value" => "",
            "domain" => $this->getSystem("cookie_domain"),
            "path" => "/admin",
            "expire" => -1,
            "secure" => false,
            "httpOnly" => true
        );
        $this->sendCookie($cookie);

        return $this->redirect($this->generateUrl("admin_index"));
    }

    public function dumpCache()
    {
        parent::dumpCache();
    }

}
