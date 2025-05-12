<?php

namespace jobseeker\Bundle\UserBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use jobseeker\Bundle\ToolBundle\Base;
use jobseeker\Bundle\ToolBundle\Service\ImageMagick;

class EmployerController extends Base
{

    protected $entityName = "UserBundle:Employer";

    public function indexAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $user = $this->getUid();

        $employer = $this->getRepo()->findOneBy(array("uid" => $user['uid']));

        if (NULL === $employer) {
            $employer = $this->getEntity();
            $employer->setUid($user['uid']);
        }

        $oldEmployer = clone $employer;

        $form = $this->createFormBuilder($employer, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("account_emr"))
            ->setMethod("post")
            ->add('name', TextType::class, array("required" => true, "data" => $employer->getName(), "attr" => array("class" => "form-control input-sm")))
            ->add('abbr', TextType::class, array("required" => true, "data" => $employer->getAbbr(), "attr" => array("class" => "form-control input-sm")))
            ->add("location", ChoiceType::class, array(
                'required' => true,
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getLocationForChoices(),
                "multiple" => false,
                'expanded' => false,
                "data" => $employer->getLocation() ? $employer->getLocation()->getId() : NULL
            ))
            ->add('address', TextType::class, array("required" => true, "data" => $employer->getAddress(), "attr" => array("class" => "form-control input-sm")))
            ->add("type", ChoiceType::class, array(
                'required' => true,
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getTypeForChoices(),
                "multiple" => false,
                'expanded' => false,
                "data" => $employer->getType() ? $employer->getType()->getId() : NULL
            ))
            ->add("scale", ChoiceType::class, array(
                'required' => true,
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getScaleForChoices(),
                "multiple" => false,
                'expanded' => false,
                "data" => $employer->getScale() ? $employer->getScale()->getId() : NULL
            ))
            ->add('contacter', TextType::class, array("required" => true, "data" => $employer->getContacter(), "attr" => array("class" => "form-control input-sm")))
            ->add('contacteremail', EmailType::class, array("required" => true, "data" => $employer->getContacteremail(), "attr" => array("class" => "form-control input-sm")))
            ->add('contactertel', TextType::class, array("required" => true, "data" => $employer->getContactertel(), "attr" => array("class" => "form-control input-sm")))
            ->add('fax', TextType::class, array("required" => false, "data" => $employer->getFax(), "attr" => array("class" => "form-control input-sm")))
            ->add("site", UrlType::class, array("required" => false, "data" => $employer->getSite(), "attr" => array("class" => "form-control input-sm")))
            ->add("about", TextareaType::class, array("required" => true, "data" => $employer->getAbout(), "attr" => array("class" => "form-control input-sm")))
            ->add("save", SubmitType::class, array("label" => $this->trans("common.save"), "attr" => array("class" => "btn btn-sm btn-primary col-2 col-offset-3")))
            ->getForm();

        if (NULL === $employer->getId()) {
            $form->add('avator', FileType::class, array(
                "required" => true
            ));
        } else {
            $form->add('avator', FileType::class, array(
                "required" => $employer->getAvator() ? false : true,
                "data" => $employer->getAvator() ? $employer->getView($this, "avator") : NULL
            ));
        }

        if (NULL !== $employer->getId()) {
            $form->add("delete", SubmitType::class, array(
                "label" => $this->trans("common.delete"),
                "attr" => array("class" => "btn btn-sm btn-warning col-2")
            ));
        }

        $renderValue = array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "form" => $form->createView()
        );

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->get("save")->isClicked()) {
                $employer->setLocation($this->getRepo("AdminBundle:Area")->find($employer->getLocation()));
                $employer->setType($this->getRepo("UserBundle:Category")->find($employer->getType()));
                $employer->setScale($this->getRepo("UserBundle:Category")->find($employer->getScale()));

                if (NULL === $employer->getId()) {
                    $employer->upload($this, "avator", $this->getAvatorRootPath());
                } else {
                    if (NULL === $employer->getAvator()) {
                        $employer->setAvator($oldEmployer->getAvator() ?: NULL);
                    } else {
                        $status = $employer->upload($this, "avator", $this->getAvatorRootPath());
                        if ($status === 1) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_type"));
                            return $this->redirect($this->generateUrl("account_emr"));
                        } else if ($status === 2) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_size"));
                            return $this->redirect($this->generateUrl("account_emr"));
                        } else if ($status === 3) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_other"));
                            return $this->redirect($this->generateUrl("account_emr"));
                        } else {
                            if ($tmp = $oldEmployer->getAvator()) {
                                $this->deleteAvator($tmp);
                            }
                        }
                    }
                }

                $this->getEm()->persist($employer);
                $this->getEm()->flush();
                $this->get("session")->getFlashBag()->add("info", $this->trans("be.update.success"));
                return $this->redirect($this->generateUrl("account_emr"));
            }

            if ($form->isValid() && $form->get("delete")->isClicked()) {
                try {
                    $this->getEm()->remove($employer);
                    $this->getEm()->flush();
                    $this->get("session")->getFlashBag()->add("info", $this->trans("be.delete.success"));
                    return $this->redirect($this->generateUrl("account_emr"));
                } catch (\Exception $e) {
                    $this->get("session")->getFlashBag()->add("info", $this->trans("be.delete.fail"));
                    return $this->redirect($this->generateUrl("account_emr"));
                }
            }
        }

        return $this->render('user/employer/index.html.twig', $renderValue);
    }

    public function newAction()
    {
        $renderValue = array();

        $renderValue["isLogin"] = false;

        $user = $this->getUid();

        if (NULL !== $user) {
            $employee = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
            $employer = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $user['uid']));
            if (NULL !== $employer) {
                $renderValue['eme_or_emr'] = $employer->serialize();
            } else if (NULL !== $employee) {
                $renderValue['eme_or_emr'] = $employee->serialize();
            } else {
                $employer = $this->getEntity();
                $employer->setUid($user['uid']);
            }
            if (true === parent::autoLogin()) {
                $renderValue["isLogin"] = true;
            }
        } else {
            $employer = $this->getEntity();
        }

        $oldEmployer = clone $employer;

        $userManager = $this->get("UserManager");

        if (NULL === $user) {
            $role = $this->get("RoleManager")->findRoleByName();
            $user = $userManager->createUser();
            $user->setRid($role);
        } else {
            $user = $userManager->findUserByUid($user["uid"]);
        }

        $user->setLoginIp($this->getClientIp());

        $formSignin = $this->createFormBuilder($user, array("attr" => array("role" => "form", "class" => "form-inline")))
            ->setAction($this->generateUrl("user_signin"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.email"), "autofocus" => '')))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.password"))))
            ->add("signin", SubmitType::class, array("label" => $this->trans("fe.signin"), "attr" => array("class" => "btn btn-info")))
            ->getForm();

        $formSignup = $this->createFormBuilder($user, array("attr" => array("role" => "form", "class" => "form-inline")))
            ->setAction($this->generateUrl("user_signup"))
            ->setMethod("post")
            ->add('email', EmailType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.email"), "autofocus" => '')))
            ->add("password", PasswordType::class, array("attr" => array("class" => "form-control", "placeholder" => $this->trans("table.user.password"))))
            ->add("signup", SubmitType::class, array("label" => $this->trans("fe.signup"), "attr" => array("class" => "btn btn-warning")))
            ->getForm();

        $emrForm = $this->createFormBuilder($employer, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("emr_new"))
            ->setMethod("post")
            ->add('name', TextType::class, array("required" => true, "data" => $employer->getName(), "attr" => array("class" => "form-control input-sm")))
            ->add('abbr', TextType::class, array("required" => true, "data" => $employer->getAbbr(), "attr" => array("class" => "form-control input-sm")))
            ->add("location", ChoiceType::class, array(
                'required' => true,
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getLocationForChoices(),
                "multiple" => false,
                'expanded' => false,
                "data" => $employer->getLocation() ? $employer->getLocation()->getId() : NULL
            ))
            ->add('address', TextType::class, array("required" => true, "data" => $employer->getAddress(), "attr" => array("class" => "form-control input-sm")))
            ->add("type", ChoiceType::class, array(
                'required' => true,
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getTypeForChoices(),
                "multiple" => false,
                'expanded' => false,
                "data" => $employer->getType() ? $employer->getType()->getId() : NULL
            ))
            ->add("scale", ChoiceType::class, array(
                'required' => true,
                "attr" => array(
                    "class" => "form-control input-sm"
                ),
                "choices" => $this->getScaleForChoices(),
                "multiple" => false,
                'expanded' => false,
                "data" => $employer->getScale() ? $employer->getScale()->getId() : NULL
            ))
            ->add('contacter', TextType::class, array("required" => true, "data" => $employer->getContacter(), "attr" => array("class" => "form-control input-sm")))
            ->add('contacteremail', EmailType::class, array("required" => true, "data" => $employer->getContacteremail(), "attr" => array("class" => "form-control input-sm")))
            ->add('contactertel', TextType::class, array("required" => true, "data" => $employer->getContactertel(), "attr" => array("class" => "form-control input-sm")))
            ->add('fax', TextType::class, array("required" => false, "data" => $employer->getFax(), "attr" => array("class" => "form-control input-sm")))
            ->add("site", UrlType::class, array("required" => false, "data" => $employer->getSite(), "attr" => array("class" => "form-control input-sm")))
            ->add("about", TextareaType::class, array("required" => true, "data" => $employer->getAbout(), "attr" => array("class" => "form-control input-sm emrform_about")))
            ->add("save", SubmitType::class, array("label" => $this->trans("common.save"), "attr" => array("class" => "btn btn-sm btn-primary col-2 col-offset-3")))
            ->getForm();

        if (NULL === $employer->getId()) {
            $emrForm->add('avator', FileType::class, array(
                "required" => true
            ));
        } else {
            $emrForm->add('avator', FileType::class, array(
                "required" => $employer->getAvator() ? false : true,
                "data" => $employer->getAvator() ? $employer->getView($this, "avator") : NULL
            ))->add("delete", SubmitType::class, array(
                "label" => $this->trans("common.delete"),
                "attr" => array("class" => "btn btn-sm btn-warning col-2")
            ));
        }

        $renderValue = array_merge($renderValue, array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "emrform" => $emrForm->createView(),
            "plugins" => $this->get("UserPluginManager")->autoLogin(),
            "formSignin" => $formSignin->createView(),
            "formSignup" => $formSignup->createView(),
        ));

        if ($this->isPost()) {
            $emrForm->handleRequest($this->getRequest());
            if ($emrForm->isValid() && $emrForm->get("save")->isClicked()) {
                if (NULL === $employer->getName()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.name")));
                } else if (NULL === $employer->getAbbr()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.abbr")));
                } else if (NULL === $employer->getType()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.type")));
                } else if (NULL === $employer->getLocation()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.location")));
                } else if (NULL === $employer->getScale()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.scale")));
                } else if (NULL === $employer->getAddress()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.address")));
                } else if (NULL === $employer->getContacter()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.contacter")));
                } else if (NULL === $employer->getContacteremail()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.contacteremail")));
                } else if (NULL === $employer->getContactertel()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.contactertel")));
                } else if (NULL === $employer->getAbout()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.employer.about")));
                } else {
                    if (NULL === $user) {
                        $this->get("session")->getFlashBag()->add("danger", "您还没有登录，请重新登录");
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

                    $employer->setLocation($this->getRepo("AdminBundle:Area")->find($employer->getLocation()));
                    $employer->setType($this->getRepo("UserBundle:Category")->find($employer->getType()));
                    $employer->setScale($this->getRepo("UserBundle:Category")->find($employer->getScale()));

                    if (NULL === $employer->getId()) {
                        $employer->upload($this, "avator", $this->getAvatorRootPath());
                    } else {
                        if (NULL === $employer->getAvator()) {
                            $employer->setAvator($oldEmployer->getAvator() ?: NULL);
                        } else {
                            $status = $employer->upload($this, "avator", $this->getAvatorRootPath());
                            if ($status === 1) {
                                $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_type"));
                                return $this->redirect($this->generateUrl("emr_new"));
                            } else if ($status === 2) {
                                $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_size"));
                                return $this->redirect($this->generateUrl("emr_new"));
                            } else if ($status === 3) {
                                $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_other"));
                                return $this->redirect($this->generateUrl("emr_new"));
                            } else {
                                if ($tmp = $oldEmployer->getAvator()) {
                                    $this->deleteAvator($tmp);
                                }
                            }
                        }
                    }

                    $this->getEm()->persist($employer);
                    $this->getEm()->flush();
                    $prefix = strtoupper($this->getParameter("country"));
                    $this->saddRedis($prefix . ":employer", $user->getUid());
                    $this->get("session")->getFlashBag()->add("info", $this->trans("be.update.success"));
                    return $this->redirect($this->generateUrl("dashboard"));
                }
            }
        }

        if ($emrForm->isValid() && $emrForm->get("delete")->isClicked()) {
            try {
                $this->getEm()->remove($employer);
                $this->getEm()->flush();
                $this->get("session")->getFlashBag()->add("info", $this->trans("be.delete.success"));
                return $this->redirect($this->generateUrl("dashboard"));
            } catch (\Exception $e) {
                $this->get("session")->getFlashBag()->add("info", $this->trans("be.delete.fail"));
                return $this->redirect($this->generateUrl("dashboard"));
            }
        }

        return $this->render('user/employer/new.html.twig', $renderValue);
    }

    private function getLocationForChoices()
    {
        $data = $this->getAreaForSearch();

        if (count($data) > 0) {
            foreach ($data as $id => $area) {
                if (isset($area['sub'])) {
                    $areaArr[$this->trans($area['name'])] = $id;
                }
            }
        }

        return $areaArr;
    }

    private function getScaleForChoices()
    {
        $data = array();

        $scales = $this->getCache("UserBundle:Employer_Scale");

        if (count($scales) > 0) {
            foreach ($scales as $scale) {
                if ($scale['status'] == 1) {
                    $data[$this->trans(strtoupper($scale['csn']))] = $scale['id'];
                }
            }
        }

        return $data;
    }

    private function getTypeForChoices()
    {
        $data = array();

        $types = $this->getCache("UserBundle:Employer_Type");

        if (count($types) > 0) {
            foreach ($types as $type) {
                if ($type['status'] == 1) {
                    $data[$this->trans(strtoupper($type['csn']))] = $type['id'];
                }
            }
        }

        return $data;
    }

    final public function dumpCache()
    {
        parent::dumpCache();
    }

}
