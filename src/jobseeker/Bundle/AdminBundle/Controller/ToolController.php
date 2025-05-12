<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use jobseeker\Bundle\AdminBundle\AdminBase;

class ToolController extends AdminBase
{

    public function addUserAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $userManager = $this->get("UserManager");
        $userEntity = $userManager->createUser();

        $formSignup = $this->createFormBuilder(null, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("add_user"))
            ->setMethod("post")
            ->add('to', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => "To:")))
            ->add('cc', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => "Cc:")))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.password"))))
            ->add("subject", TextType::class, array("attr" => array("class" => "form-control", "placeholder" => "标题")))
            ->add("template", ChoiceType::class, array(
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => array(
                    "新建用户" => "add_user",
                    "自定义" => "auto_define"
                ),
                'placeholder' => "模板",
                "multiple" => false,
                'required' => true,
                'expanded' => false,
            ))
            ->add("signup", SubmitType::class, array("label" => $this->trans("fe.signup"), "attr" => array("class" => "btn btn-info")))
            ->getForm();

        if ($this->isPost()) {
            $formSignup->handleRequest($this->getRequest());
            if ($formSignup->isValid() && $formSignup->get("signup")->isClicked()) {
                $country = $this->getSystem("country") ?: "cn";
                $formData = $this->getFormData();
                $ssoUser = $userManager->createSsoUser($formData["to"], $formData["password"], $country, "robot", false);
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
                    $validateURL = $this->generateUrl("user_validate", array("code" => $validateCode), true);
                    $this->sendUserValidateEmail($ssoUser['email'], $validateURL, $formData["cc"]);
                    $this->get("session")->getFlashBag()->add("success", $this->trans("user.signup.success"));

                    return $this->redirect($this->generateUrl("add_user"));
                }
            } else {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.invalid"));
            }
        }

        return $this->render('admin/tool/add_user.html.twig', array("formSignup" => $formSignup->createView()));
    }

}
