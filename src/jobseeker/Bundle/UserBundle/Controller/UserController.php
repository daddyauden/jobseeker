<?php

namespace jobseeker\Bundle\UserBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use jobseeker\Bundle\ToolBundle\Base;

class UserController extends Base
{

    protected $entityName = "UserBundle:User";

    public function signupAction()
    {
        $userManager = $this->get("UserManager");
        $userEntity = $userManager->createUser();

        $formSignup = $this->createFormBuilder($userEntity, array("attr" => array("role" => "form", "class" => "form-inline")))
            ->setAction($this->generateUrl("user_signup"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.email"), "autofocus" => '')))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.password"))))
            ->add("signup", SubmitType::class, array("label" => $this->trans("fe.signup"), "attr" => array("class" => "btn btn-info")))
            ->getForm();

        if ($this->isPost()) {
            $formSignup->handleRequest($this->getRequest());
            if ($formSignup->isValid() && $formSignup->get("signup")->isClicked()) {
                if (false !== $ssoUser = $userManager->findSsoUserByEmail($userEntity->getEmail())) {
                    $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.exist"));
                } else {
                    $country = $this->getSystem("country") ?: "cn";
                    $ssoUser = $userManager->createSsoUser($userEntity->getEmail(), $userEntity->getPassword(), $country);
                    if ($ssoUser === true) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.exist"));
                    } else if ($ssoUser === false) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.error"));
                    } else if (!isset($ssoUser['uid'])) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.error"));
                    } else {
                        $role = $this->get("RoleManager")->findRoleByName();
                        $userEntity->setRid($role);
                        $userEntity->setUid($ssoUser['uid']);
                        $userEntity->setLogintime($ssoUser['addtime']);
                        $userEntity->setLoginIp($this->getClientIp());
                        $userEntity->setEmail(null);
                        $userEntity->setPassword(null, true);
                        $userManager->updateUser($userEntity);
                        $validateCode = $this->encode(array("salt" => $ssoUser["salt"], "uid" => $ssoUser['uid']));
                        $validateURL = $this->getSchemeAndHttpHost() . $this->generateUrl("user_validate", array("code" => $validateCode), true);
                        $this->sendUserValidateEmail($ssoUser['email'], $validateURL);
                        $this->get("session")->getFlashBag()->add("success", $this->trans("user.signup.success"));

                        $referer = $this->getRequest()->headers->get('referer');
                        $jobsearchURL = $this->generateurl("job_post");
                        if (strpos($referer, $jobsearchURL) !== false) {
                            return $this->redirect($jobsearchURL);
                        } else {
                            return $this->redirect($this->generateUrl("index"));
                        }
                    }
                }
            } else {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.invalid"));
            }
        }

        return $this->render('user/user/signup.html.twig', array(
            "plugins" => $this->get("UserPluginManager")->autoLogin(),
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "brand_intro" => $this->brandIntro(),
            "formSignup" => $formSignup->createView()
        ));
    }

    public function signinAction()
    {
        $userManager = $this->get("UserManager");
        $userEntity = $userManager->createUser();

        $formSignin = $this->createFormBuilder($userEntity, array("attr" => array("role" => "form", "class" => "form-inline")))
            ->setAction($this->generateUrl("user_signin"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.email"), "autofocus" => '')))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.password"))))
            ->add("signin", SubmitType::class, array("label" => $this->trans("fe.signin"), "attr" => array("class" => "btn btn-info")))
            ->getForm();

        if ($this->isPost()) {
            $formSignin->handleRequest($this->getRequest());
            if ($formSignin->isValid() && $formSignin->isSubmitted()) {
                if (false === $userManager->findSsoUserByEmail($userEntity->getEmail())) {
                    $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.noexist"));
                } else {
                    $ssoUser = $userManager->findSsoUserByEP($userEntity->getEmail(), $userEntity->getPassword());
                    if (false === $ssoUser) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signin.error_password"));
                    } else {
                        $prefix = strtoupper($this->getParameter("country"));
                        $hkey = $prefix . ":user:" . $ssoUser["uid"];
                        if ($this->existsRedis($hkey)) {
                            foreach ($ssoUser as $key => $value) {
                                $this->hsetRedis($hkey, $key, $value);
                            }
                        } else {
                            $this->hmsetRedis($hkey, $ssoUser);
                        }
                        $userEntity = $userManager->findUserByUid($ssoUser["uid"]);
                        if (null === $userEntity) {
                            $role = $this->get("RoleManager")->findRoleByName();
                            $userEntity = $userManager->createUser();
                            $userEntity->setUid($ssoUser["uid"]);
                            $userEntity->setRid($role);
                        }
                        $timestamp = time();
                        $userEntity->setLogintime($timestamp);
                        $userEntity->setLoginIp($this->getClientIp());
                        $userEntity->setEmail(null);
                        $userEntity->setPassword(null, true);
                        $userManager->updateUser($userEntity);
                        $user_data = $userEntity->serialize(array("status" => $ssoUser["status"], "email" => $ssoUser["email"], "source" => $ssoUser["source"], "country" => $ssoUser["country"]));
                        $cookie = array(
                            "name" => "UID",
                            "value" => $this->encodeCookie($user_data),
                            "expire" => $this->printCookieExpire($timestamp),
                            "domain" => $this->getSystem("domain"),
                            "path" => "/",
                            "secure" => false,
                            "httpOnly" => true
                        );
                        $this->sendCookie($cookie);

                        $referer = $this->getRequest()->headers->get('referer');
                        $jobsearchURL = $this->generateurl("job_post");
                        if (strpos($referer, $jobsearchURL) !== false) {
                            return $this->redirect($jobsearchURL);
                        } else {
                            return $this->redirect($this->generateUrl("index"));
                        }
                    }
                }
            } else {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signin.invalid"));
            }
        }

        return $this->render('user/user/signin.html.twig', array(
            "plugins" => $this->get("UserPluginManager")->autoLogin(),
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "brand_intro" => $this->brandIntro(),
            "formSignin" => $formSignin->createView(),
        ));
    }

    public function logoutAction()
    {
        $userPlugin = $this->get("UserPluginManager");
        $userPlugin->removeState();
        $userPlugin->removeToken();
        $cookie = array(
            "name" => "UID",
            "value" => "",
            "domain" => $this->getSystem("domain"),
            "path" => "/",
            "expire" => -1,
            "secure" => false,
            "httpOnly" => true
        );
        $this->sendCookie($cookie);

        return $this->redirect($this->generateUrl("index"));
    }

    public function validateAction($code)
    {
        $renderValue = array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "brand_intro" => $this->brandIntro()
        );

        if (!$code) {
            $this->get("session")->getFlashBag()->add("danger", $this->trans("user.validate.invalid"));
        } else {
            try {
                $code = $this->decode($code);
            } catch (\Exception $e) {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.validate.invalid"));
            }

            if (!isset($code['salt']) || !isset($code['uid'])) {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.validate.invalid"));
            } else {
                $userManager = $this->get("UserManager");
                $ssoUser = $userManager->activeSsoUser($code['salt'], $code['uid']);
                if (false === $ssoUser) {
                    $this->get("session")->getFlashBag()->add("danger", $this->trans("user.validate.invalid"));
                } else {
                    $userEntity = $userManager->findUserByUid($ssoUser["uid"]);
                    if (null === $userEntity) {
                        $role = $this->get("RoleManager")->findRoleByName();
                        $userEntity = $userManager->createUser();
                        $userEntity->setUid($ssoUser["uid"]);
                        $userEntity->setRid($role);
                    }
                    $timestamp = time();
                    $userEntity->setLogintime($timestamp);
                    $userEntity->setLoginIp($this->getClientIp());
                    $userEntity->setEmail(null);
                    $userEntity->setPassword(null, true);
                    $userManager->updateUser($userEntity);
                    $cookie = array(
                        "name" => "UID",
                        "value" => $this->encodeCookie($userEntity->serialize(array("status" => $ssoUser["status"], "email" => $ssoUser["email"], "source" => $ssoUser["source"], "country" => $ssoUser["country"]))),
                        "expire" => $this->printCookieExpire($timestamp),
                        "domain" => $this->getSystem("domain"),
                        "path" => "/",
                        "secure" => false,
                        "httpOnly" => true
                    );
                    $this->sendCookie($cookie);
                    $this->get("session")->getFlashBag()->add("success", $this->trans("user.validate.success", array("%site%" => $this->generateUrl("dashboard") . "?q=account_eme")));
                }
            }
        }

        return $this->render("user/user/validate.html.twig", $renderValue);
    }

}
