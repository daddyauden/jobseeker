<?php

namespace jobseeker\Bundle\SsoBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use jobseeker\Bundle\ToolBundle\Base;

class OAuthController extends Base
{

    protected $entityName = "SsoBundle:User";

    public function authAction()
    {
        $oauth2 = $this->get("oauth2");
        if ($this->isPost()) {
            $request = $this->getRequest()->duplicate($this->getFormData());
            $response = $oauth2->validateAuthorizeRequest($request);
        } else {
            $request = $this->getRequest();
            $response = $oauth2->validateAuthorizeRequest($request);
        }

        if (true !== $response) {
            $error = json_decode($response->getContent(), true);
            $this->get("session")->getFlashBag()->add("danger", $error['error']['message']);

            return $this->render("SsoBundle:OAuth:auth.html.twig");
        }

        $client = $oauth2->getAuthorizeController();

        $form = $this->createFormBuilder(null, array("attr" => array("role" => "form", "class" => "form")))
            ->setAction($this->generateUrl("auth"))
            ->setMethod("post")
            ->add('client_id', HiddenType::class, array("data" => $client->getClientId()))
            ->add('redirect_uri', HiddenType::class, array("data" => $client->getRedirectUri()))
            ->add('response_type', HiddenType::class, array("data" => $client->getResponseType()))
            ->add('state', HiddenType::class, array("data" => $client->getState()))
            ->add('scope', HiddenType::class, array("data" => $client->getScope()))
            ->add('is_authorized', HiddenType::class, array("data" => 1))
            ->add('email', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.email"), "autofocus" => '')))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.password"))))
            ->add("signin", SubmitType::class, array("label" => $this->trans("fe.signin"), "attr" => array("class" => "btn btn-primary")))
            ->add("cancel", ButtonType::class, array("label" => $this->trans("fe.cancel"), "attr" => array("class" => "btn btn-danger")))
            ->getForm();

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("signin")->isClicked()) {
                $userExist = $oauth2->getStorage("user_credentials")->checkUserCredentials($this->getFormData("email"), $this->getFormData("password"));
                if (true === $userExist) {
                    try {
                        $is_authorized = $this->getFormData("is_authorized") ? true : false;
                        $user = $oauth2->getStorage("user_credentials")->getUserDetail($this->getFormData("email"));
                        $uidEncode = base64_encode($user['uid']);
                        $cookie = $this->createCookie(array(
                            "name" => "uid",
                            "value" => $user['uid'],
                            "domain" => ".jobseeker.com",
                            "expire" => time() + 3600 * 24 * 7,
                            "path" => "/",
                            "secure" => false,
                            "httpOnly" => true
                        ));
                        $response = $oauth2->handleAuthorizeRequest($request, $is_authorized, $user['uid']);
                        $response->headers->setCookie($cookie);
                        return $response;
                    } catch (\Exception $e) {
                        $this->get("session")->getFlashBag()->add("danger", "Signin Error");
                    }
                } else {
                    $this->get("session")->getFlashBag()->add("danger", "Signin Error");
                }
            }
        }

        return $this->render("SsoBundle:OAuth:auth.html.twig", array('form' => $form->createView()));
    }

    public function tokenAction()
    {
        $oauth2 = $this->get("oauth2");

        return $oauth2->handleTokenRequest($this->getRequest());
    }

    public function registerAction()
    {
        $result = array();
        if ($this->isPost()) {

        } else {
            $result['error']['code'] = SsoException::METHOD_INVALID;
            $result['error']['message'] = self::$errorText[SsoException::METHOD_INVALID];
        }
        return $this->render('SsoBundle:Index:index.html.twig', array('name' => $name));
    }

    public function getAction($name)
    {
        $result = array();
        if ($this->isPost()) {
            $auth = $this->getRequest()->getContent();
            $apphost = $this->getRequest()->getHost();
            if (!isset($auth['appkey'])) {
                $result['error']['code'] = SsoException::MISS_APPKEY;
                $result['error']['message'] = self::$errorText[SsoException::MISS_APPKEY];
            } elseif (!isset($auth['appsecret'])) {
                $result['error']['code'] = SsoException::MISS_APPSECRET;
                $result['error']['message'] = self::$errorText[SsoException::MISS_APPSECRET];
            } else {
                $isAuthenticated = $this->getRepo("SsoBundle:Authorize")->isAuthenticated($auth['appkey'], $auth['appsecret'], $apphost);
            }
        } else {
            $result['error']['code'] = SsoException::METHOD_INVALID;
            $result['error']['message'] = self::$errorText[SsoException::METHOD_INVALID];
        }
        return $this->render('SsoBundle:Index:index.html.twig', array('name' => $name));
    }

}
