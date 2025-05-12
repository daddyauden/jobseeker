<?php

namespace jobseeker\Bundle\CareerBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use jobseeker\Bundle\ToolBundle\Base;
use jobseeker\Bundle\CareerBundle\DependencyInjection\JobEncryptInterface;

class JobController extends Base
{

    protected $entityName = "CareerBundle:Job";

    public function postAction()
    {
        $renderValue = array();

        $renderValue["isLogin"] = false;

        $user = $this->getUid();

        $ssoUser = $user;

        $userManager = $this->get("UserManager");

        if (NULL !== $user) {
            $employeeEntity = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
            $employerEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $user['uid']));
            if (NULL !== $employerEntity) {
                $renderValue['eme_or_emr'] = $employerEntity->serialize();
            } else if (NULL !== $employeeEntity) {
                $renderValue['eme_or_emr'] = $employeeEntity->serialize();
            }

            if (true === parent::autoLogin()) {
                $renderValue["isLogin"] = true;
            }

            $user = $userManager->findUserByUid($user["uid"]);
        } else {
            $role = $this->get("RoleManager")->findRoleByName();
            $user = $userManager->createUser();
            $user->setRid($role);
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

        $jobEntity = $this->getEntity("CareerBundle:Job");

        $jobForm = $this->createFormBuilder($jobEntity, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("job_post"))
            ->setMethod("post")
            ->add("product", HiddenType::class)
            ->add('industry', HiddenType::class)
            ->add('area', HiddenType::class)
            ->add('salary', NumberType::class, array("required" => true, "attr" => array("class" => "form-control input-sm")))
            ->add('type', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => $this->getTypeForSearch(),
                'placeholder' => $this->trans("common.choose"),
            ))
            ->add('begintime', TextType::class, array(
                "attr" => array(
                    "class" => "form-control input-sm input-date job_post_datetime",
                    "data-date-format" => $this->convertDateTimeFormatForJS(),
                    "startdate" => date($this->getSystem("datetime_format"), strtotime("+1 day")),
                ),
                "required" => true,
                "data" => date($this->getSystem("datetime_format"), strtotime("+1 day")),
            ))
            ->add('title', TextType::class, array("required" => true, "attr" => array("class" => "form-control input-sm")))
            ->add('description', TextareaType::class, array("required" => true, "attr" => array("class" => "form-control input-sm jobform_description")))
            ->add('contacter', TextType::class, array("required" => false, "attr" => array("class" => "form-control input-sm")))
            ->add('contacteremail', EmailType::class, array("required" => false, "attr" => array("class" => "form-control input-sm")))
            ->add('contactertel', TextType::class, array("required" => false, "attr" => array("class" => "form-control input-sm")))
            ->add("about", TextareaType::class, array("required" => false, "attr" => array("class" => "form-control input-sm jobform_about")))
            ->add('avator', FileType::class, array("required" => false))
            ->add("save", SubmitType::class, array(
                "label" => $this->trans("common.save"),
                "attr" => array("class" => "btn btn-primary col-3 col-offset-3")
            ))
            ->getForm();

        if ($this->isPost()) {
            $jobForm->handleRequest($this->getRequest());
            if ($jobForm->isValid() && $jobForm->get("save")->isClicked()) {
                if (NULL === $jobEntity->getProduct()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.product")));
                } else if (NULL === $jobEntity->getIndustry()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.industry")));
                } else if (NULL === $jobEntity->getType()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.type")));
                } else if (NULL === $jobEntity->getArea()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.area")));
                } else if (NULL === $jobEntity->getBegintime()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.begintime")));
                } else if (NULL === $jobEntity->getSalary()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.salary")));
                } else if (NULL === $jobEntity->getTitle()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.title")));
                } else if (NULL === $jobEntity->getDescription()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.description")));
                } else {
                    if (NULL === $user->getId()) {
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
                    $this->detectJid($jobEntity);
                    $productId = (int)$jobEntity->getProduct();
                    if (isset($ssoUser['email']) && strpos($ssoUser['email'], "@jobseeker.com")) {
                        $jobEntity->setVerify();
                    }
                    $jobEntity->setCtime(time());
                    $prefix = strtoupper($this->getParameter("country"));
                    if ($this->existsRedis($prefix . ":product:" . $productId)) {
                        $duration = $this->hgetRedis($prefix . ":product:" . $productId, 'duration');
                    } else {
                        $duration = 3600 * 24 * 90;
                    }
                    $status = $jobEntity->upload($this, "avator", $this->getAvatorRootPath());
                    if ($status === 1) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_type"));
                    } else if ($status === 2) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_size"));
                    } else if ($status === 3) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_other"));
                    }
                    $jobEntity->setEndtime(doubleval($duration) + doubleval($jobEntity->getBegintime()));
                    $jobEntity->setCtime(time());
                    $jobEntity->setUid($user->getUid());
                    $this->getEm()->persist($jobEntity);
                    $this->getEm()->flush();
                    if ($employerEntity == NULL) {
                        return $this->redirect($this->generateUrl("emr_new"));
                    } else {
                        return $this->redirect($this->generateUrl("dashboard"));
                    }
                }
            }
        }

        $renderValue = array_merge($renderValue, array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "jobform" => $jobForm->createView(),
            "industry" => $this->getIndustryForSearch(),
            "area" => $this->getAreaForSearch(),
            "product" => $this->getProductForSearch(),
            "productDefault" => array_keys($this->getProductForSearch()),
            "plugins" => $this->get("UserPluginManager")->autoLogin(),
            "formSignin" => $formSignin->createView(),
            "formSignup" => $formSignup->createView()
        ));

        return $this->render("career/job/post.html.twig", $renderValue);
    }

    private function detectJid($jobEntity)
    {
        $res = $this->getRepo()->findBy(array("jid" => $jobEntity->getJid()));
        while ($res) {
            $this->detectJid($jobEntity->setJid());
        }

        return;
    }

    public function editAction($condition)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("brand"));
        }

        $jobData = NULL;

        if ($this->isGet()) {
            $jobData = $this->decode($condition, JobEncryptInterface::SEARCH_CONDITION_SALT);
        }

        if ($this->isPost()) {
            $condition = $this->getFormData("jid");
            if ($condition) {
                $jobData = $this->decode($condition, JobEncryptInterface::SEARCH_CONDITION_SALT);
            }
        }

        $user = $this->getUid();

        if (NULL === $jobData || NULL === $user) {
            return $this->redirect($this->generateurl("brand"));
        }

        $jobEntity = $this->getRepo()->findOneBy(array("uid" => $user['uid'], "id" => $jobData['id']));

        if (NULL === $jobEntity) {
            return $this->redirect($this->generateUrl("brand"));
        }

        $oldJobEntity = clone $jobEntity;

        $form = $this->createFormBuilder($jobEntity, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("job_edit"))
            ->setMethod("post")
            ->add('salary', NumberType::class, array("required" => true, "data" => $jobEntity->getSalary(), "attr" => array("class" => "form-control input-sm")))
            ->add('title', TextType::class, array("required" => true, "data" => $jobEntity->getTitle(), "attr" => array("class" => "form-control input-sm")))
            ->add('description', TextareaType::class, array("required" => true, "data" => $jobEntity->getDescription(), "attr" => array("class" => "form-control input-sm")))
            ->add('contacter', TextType::class, array("required" => false, "data" => $jobEntity->getContacter(), "attr" => array("class" => "form-control input-sm")))
            ->add('contacteremail', EmailType::class, array("required" => false, "data" => $jobEntity->getContacteremail(), "attr" => array("class" => "form-control input-sm")))
            ->add('contactertel', TextType::class, array("required" => false, "data" => $jobEntity->getContactertel(), "attr" => array("class" => "form-control input-sm")))
            ->add("about", TextareaType::class, array("required" => false, "data" => $jobEntity->getAbout(), "attr" => array("class" => "form-control input-sm")))
            ->add('avator', FileType::class, array("required" => false, "data" => $jobEntity->getAvator() ? $jobEntity->getView($this, "avator") : NULL))
            ->add("save", SubmitType::class, array(
                "label" => $this->trans("common.save"),
                "attr" => array("class" => "btn btn-primary col-2")
            ))
            ->getForm();

        if (NULL === $jobEntity->getId()) {
            $form->add('avator', FileType::class, array(
                "required" => false
            ));
        } else {
            $form->add('avator', FileType::class, array(
                "required" => false,
                "data" => $jobEntity->getAvator() ? $jobEntity->getView($this, "avator") : NULL
            ));
        }

        $renderValue = array(
            "jid" => $condition
        );

        if ($jobEntity->getBegintime() > time() + 3600 * 3) {
            $form->add("product", HiddenType::class)
                ->add('type', HiddenType::class)
                ->add('industry', HiddenType::class)
                ->add('area', HiddenType::class)
                ->add('begintime', TextType::class, array(
                    "required" => true,
                    "attr" => array(
                        "class" => "form-control input-sm input-date job_post_datetime",
                        "data-date-format" => $this->convertDateTimeFormatForJS(),
                        "startdate" => date($this->getSystem("datetime_format"), time() + 1800),
                    ),
                    "data" => date($this->getSystem("datetime_format"), $jobEntity->getBegintime()),
                ));
            $renderValue["product"] = $this->getProductForSearch();
            $renderValue["type"] = $this->getTypeForSearch();
            $renderValue["industry"] = $this->getIndustryForSearch();
            $renderValue["area"] = $this->getAreaForSearch();
        }

        $renderValue["form"] = $form->createView();

        $tmp = array();

        foreach (array("product", "type", "industry", "area") as $key) {
            $name = "get" . ucfirst($key);
            if (NULL !== ${$key} = $jobEntity->$name()) {
                $value = explode(",", ${$key});
                $ids = array_map(function ($id) {
                    return intval($id);
                }, $value);

                $tmp[$key] = $ids;

                if ($key == "industry") {
                    $industries = $this->getCache("CareerBundle:Industry");
                    $pid = (int)$industries[$tmp[$key][0]]['pid'];
                    $renderValue[$key . "Default"] = array($pid, $tmp[$key]);
                } else if ($key == "area") {
                    $areas = $this->getCache("AdminBundle:Area");
                    $pid = (int)$areas[$tmp[$key][0]]['pid'];
                    $renderValue[$key . "Default"] = array($pid, $tmp[$key]);
                } else {
                    $renderValue[$key . "Default"] = $tmp[$key];
                }
            }
        }

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->get("save")->isClicked()) {
                if (NULL === $jobEntity->getProduct()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.product")));
                } else if (NULL === $jobEntity->getIndustry()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.industry")));
                } else if (NULL === $jobEntity->getType()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.type")));
                } else if (NULL === $jobEntity->getArea()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.area")));
                } else if (NULL === $jobEntity->getBegintime()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.begintime")));
                } else if (NULL === $jobEntity->getSalary()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.salary")));
                } else if (NULL === $jobEntity->getTitle()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.title")));
                } else if (NULL === $jobEntity->getDescription()) {
                    $this->get("session")->getFlashBag()->add("danger", sprintf("%s 没有设置", $this->trans("table.job.description")));
                } else {
                    if ($oldJobEntity == $jobEntity) {
                        $this->get("session")->getFlashBag()->add("danger", $this->trans("be.nochange"));
                    } else {
                        try {
                            $productId = (int)$jobEntity->getProduct();
                            $jobEntity->setBegintime($jobEntity->getBegintime());

                            $prefix = strtoupper($this->getParameter("country"));
                            if ($this->existsRedis($prefix . ":product:" . $productId)) {
                                $duration = $this->hgetRedis($prefix . ":product:" . $productId, 'duration');
                            } else {
                                $duration = 3600 * 24 * 90;
                            }

                            if (NULL === $jobEntity->getId()) {
                                $jobEntity->upload($this, "avator", $this->getAvatorRootPath());
                            } else {
                                if (NULL === $jobEntity->getAvator()) {
                                    $jobEntity->setAvator($oldJobEntity->getAvator() ?: NULL);
                                } else {
                                    $status = $jobEntity->upload($this, "avator", $this->getAvatorRootPath());
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
                                        if ($tmp = $oldJobEntity->getAvator()) {
                                            $this->deleteAvator($tmp);
                                        }
                                    }
                                }
                            }
                            
                            $jobEntity->setEndtime(doubleval($duration) + doubleval($jobEntity->getBegintime()));
                            $this->getEm()->persist($jobEntity);
                            $this->getEm()->flush();
                            $this->get("session")->getFlashBag()->add("info", $this->trans("be.update.success"));
                            return $this->redirect($this->generateUrl("job_edit", array("condition" => $condition)));
                        } catch (\Exception $e) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("be.update.fail"));
                        }
                    }
                }
            } else {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("be.update.invalid"));
            }
        }

        return $this->render("career/job/edit.html.twig", $renderValue);
    }

    public function showAction($condition)
    {
        $referer = $this->getRequest()->headers->get('referer');
        $jobsearchURL = $this->generateurl("job_search", array(), true);
        if ($referer === NULL || strpos($referer, $jobsearchURL) === false) {
            return $this->redirect($this->generateurl("index"));
        } else {
            $job = $this->decode($condition, JobEncryptInterface::SEARCH_CONDITION_SALT);
            $job = $this->getRepo()->getJobBy(array("id", $job['id']));

            $emr = $this->getRepo("UserBundle:Employer")->getByUser(array("uid", $job['uid']));

            if (count($emr) > 0) {
                $delivery = array();
                $job['uid'] = $emr;
                $job['about'] = $job['about'] ?: NULL;
                $job['about2'] = $emr["about"] ?: NULL;
                $job['contacter'] = $job['contacter'] ?: NULL;
                $job['contacter2'] = $emr["contacter"] ?: NULL;
                $job['contacteremail'] = $job['contacteremail'] ?: NULL;
                $job['contacteremail2'] = $emr["contacteremail"] ?: NULL;
                $job['contactertel'] = $job['contactertel'] ?: NULL;
                $job['contactertel2'] = $emr["contactertel"] ?: NULL;

                $user = $this->getUid();

                if (NULL == $user) {
                    $job["isLogin"] = false;
                    $delivery['eme'] = NULL;
                } else {
                    $job["isLogin"] = true;
                    $employee = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
                    $education = $this->getRepo("UserBundle:Education")->findOneBy(array("uid" => $user['uid']));

                    if (NULL === $employee || NULL === $education) {
                        $job['eme'] = array();
                        if (NULL === $employee) {
                            $job['eme']["employee"] = NULL;
                        }
                        if (NULL === $education) {
                            $job['eme']["education"] = NULL;
                        }
                        $delivery['eme'] = NULL;
                    } else {
                        $delivery['eme'] = $user['uid'];
                    }
                }

                $delivery['emr'] = $job['uid']["uid"]["uid"];
                $delivery['email'] = $job['contacteremail'] ?: $emr["contacteremail"] ?: $emr["uid"]["email"] ?: NULL;
                $delivery['jid'] = $job["id"];

                if ($delivery['eme'] && $delivery['emr'] && $delivery['email']) {
                    $job["delivery"] = $this->encode($delivery, JobEncryptInterface::DELIVERY_SALT);
                }

                if ($delivery['emr'] && $delivery['email']) {
                    $entity = $this->getEntity("CareerBundle:SendCV");
                    $entity->setJid($delivery['jid']);
                    $entity->setEmr($delivery['emr']);
                    $entity->setEmailto($delivery['email']);
                    $uploader = $this->generateUrl("job_sendmail");
                    $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form", "id" => "qq-form")))
                        ->setAction($uploader)
                        ->setMethod("post")
                        ->add('jid', HiddenType::class, array("required" => true, "data" => $delivery['jid']))
                        ->add('emr', HiddenType::class, array("required" => true, "data" => $delivery['emr']))
                        ->add('emailto', HiddenType::class, array("required" => true, "data" => $delivery['email']))
                        ->add('emailfrom', TextType::class, array("required" => false, "attr" => array("class" => "form-control input-sm col-6", "placeholder" => $this->trans("job.sendcv.emailfrom"))))
                        ->add('cv', HiddenType::class, array("required" => false, "attr" => array("class" => "form-control", "multiple" => "false")))
                        ->add("save", SubmitType::class, array("label" => $this->trans("job.sendcv.send"), "attr" => array("class" => "btn btn-sm btn-info")))
                        ->getForm();

                    $job["sendmail"] = array(
                        "uploader" => $uploader,
                        "condition" => $condition,
                        "form" => $form->createView()
                    );
                }
            }

            return $this->render("career/job/show.html.twig", array("job" => $job));
        }
    }

    public function listAction($page)
    {
        $rendValue = array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "jobs" => [],
            "pages" => []
        );

        $user = $this->getUid();

        $offset = $this->getPerPage();
        $pager = $this->generatePager("/job/list-%d", $page, $offset, $this->getRepo()->getTotalByUid($user['uid']));

        $jobs = $this->getRepo()->getAllForPagerByUid($user['uid'], $page, $offset);

        if (count($jobs) > 0) {
            foreach ($jobs as $id => $job) {
                $jobs[$id]["condition"] = $this->encode(array("id" => $job['id']), JobEncryptInterface::SEARCH_CONDITION_SALT);
            }
            $rendValue["jobs"] = $jobs;
            $rendValue["pages"] = $pager->generateUrl();
        }

        return $this->render("career/job/list.html.twig", $rendValue);
    }

    public function searchAction($condition)
    {
        try {
            $searchCondition = $this->decode($condition, JobEncryptInterface::SEARCH_CONDITION_SALT);
        } catch (\Exception $e) {
            return $this->redirect($this->generateUrl("index"));
        }

        $referer = $this->getRequest()->headers->get('referer');
        $searchURL = $this->generateurl("search", array(), true);
        $jobsearchURL = $this->generateurl("job_search", array(), true);

        if ($referer === NULL || (strpos($referer, $jobsearchURL) === false && strpos($referer, $searchURL) === false)) {
            return $this->redirect($this->generateurl("search"));
        } else {
            // when user dont choose any condition, return none data
//            if (count($searchCondition) > 0) {
            $urls = array();
            if (isset($searchCondition['currentPage'])) {
                $currentPage = (int)$searchCondition['currentPage'] > 1 ? (int)$searchCondition['currentPage'] : 1;
                unset($searchCondition['currentPage']);
            } else {
                $currentPage = 1;
            }

            if (isset($searchCondition['offset'])) {
                $offset = (int)$searchCondition['offset'] > $this->getPerPage() ? (int)$searchCondition['offset'] : $this->getPerPage();
                unset($searchCondition['offset']);
            } else {
                $offset = $this->getPerPage();
            }

            $needTotal = true;
            $jobs = $this->getRepo()->getAllForPager($searchCondition, $needTotal, $currentPage, $offset);
            if ($needTotal === true) {
                $total = intval(array_pop($jobs));
            }

            $pages = intval(ceil($total / $offset)) ?: 1;

            if ($pages > 1) {
                if ($currentPage > 1 && $currentPage <= $pages) {
                    $searchCondition['currentPage'] = $currentPage - 1;
                    $currentURL = $this->generateUrl("job_search", array("condition" => $this->encode($searchCondition, JobEncryptInterface::SEARCH_CONDITION_SALT)));
                    $urls["previous"] = '<a href="' . $currentURL . '">' . $this->trans("common.page.previous") . '</a>';
                }

                $urls["current"] = '<span>' . $currentPage . " / " . $pages . '</span>';

                if ($currentPage >= 1 && $currentPage < $pages) {
                    $searchCondition['currentPage'] = $currentPage + 1;
                    $currentURL = $this->generateUrl("job_search", array("condition" => $this->encode($searchCondition, JobEncryptInterface::SEARCH_CONDITION_SALT)));
                    $urls["next"] = '<a href="' . $currentURL . '">' . $this->trans("common.page.next") . '</a>';
                }
            }

            foreach ($jobs as $id => $job) {
                $jobs[$id]["condition"] = $this->encode(array("id" => $job['id']), JobEncryptInterface::SEARCH_CONDITION_SALT);
            }

            if (count($urls) > 0) {
                return $this->render("career/job/search.html.twig", array("jobs" => $jobs, "pages" => $urls));
            } else {
                return $this->render("career/job/search.html.twig", array("jobs" => $jobs));
            }
            // when user dont choose any condition, return none data
//            } else {
//                return $this->render("career/job/search.html.twig");
//            }
        }
    }

    public function viewAction($condition)
    {
        $referer = $this->getRequest()->headers->get('referer');
        $emeDeliveryURL = $this->generateurl("eme_delivery", array(), true);
        if ($referer === NULL || strpos($referer, $emeDeliveryURL) === false) {
            return $this->redirect($this->generateurl("index"));
        } else {
            try {
                $renderValue = array();
                $delivery = $this->decode($condition, JobEncryptInterface::JOB_VIEW_SALT);

                $renderValue["schedule"] = $this->encode($delivery, JobEncryptInterface::SCHEDULE_SALT);

                $deliveryArr = $this->getRepo("CareerBundle:Delivery")->getById($delivery['id']);
                if (NULL !== $deliveryArr) {
                    $renderValue["delivery"] = $deliveryArr;
                    if ($deliveryArr["reserve"]) {
                        $renderValue["delivery"]["reserve"] = unserialize($deliveryArr["reserve"]);
                    }
                }

                $job = $this->getRepo()->getJobBy(array("id", $delivery['jid']));
                if (count($job) > 0) {
                    $renderValue["job"] = $job;
                }
                $emr = $this->getRepo("UserBundle:Employer")->getByUser(array("uid", $delivery['emruid']));
                if (count($emr) > 0) {
                    $renderValue["emr"] = $emr;
                }
                return $this->render("career/job/view.html.twig", $renderValue);
            } catch (\Exception $e) {
                return $this->redirect($this->generateurl("index"));
            }
        }
    }

//    private function getLocationForChoices()
//    {
//        $prefix = $this->container->getParameter("country");
//        $data = array();
//        $areaArr = array();
//
//        if ($this->existsRedis($prefix . ":area")) {
//            $areas = $this->hgetallRedis($prefix . ":area");
//            foreach ($areas as $id => $code) {
//                $data[$id]["name"] = $code;
//                if ($this->existsRedis($prefix . ":area:" . $id)) {
//                    $subAreas = $this->hgetallRedis($prefix . ":area:" . $id);
//                    foreach ($subAreas as $subid => $subCode) {
//                        $data[$id]["sub"][$subid] = $subCode;
//                    }
//                }
//            }
//        } else {
//            $cities = $this->getRepo("AdminBundle:Area")->getAvailableCity($this->getSystem("country"));
//            if (count($cities) > 0) {
//                foreach ($cities as $area) {
//                    $level = (int)$area['level'];
//                    $id = (int)$area['id'];
//                    $pid = (int)$area['pid'];
//                    $code = strtolower($area['code']);
//                    if ($level === 3) {
//                        $data[$id]['name'] = $code;
//                        $this->hsetRedis($prefix . ":area", $id, $code);
//                    } else if ($level === 4) {
//                        $data[$pid]['sub'][$id] = $code;
//                        $this->hsetRedis($prefix . ":area:" . $pid, $id, $code);
//                    } else {
//                        continue;
//                    }
//                }
//            }
//        }
//
//        if (count($data) > 0) {
//            foreach ($data as $id => $area) {
//                if (isset($area['sub'])) {
//                    $areaArr[$id] = $this->trans($area['name']);
//                }
//            }
//        }
//
//        return $areaArr;
//    }

//    private function getScaleForChoices()
//    {
//        $data = array();
//
//        $scales = $this->getCache("UserBundle:Employer_Scale");
//        if (count($scales) == 0) {
//            $scales = $this->getRepo("UserBundle:Category")->getAll("employer_scale");
//        }
//
//        if (count($scales) > 0) {
//            foreach ($scales as $scale) {
//                if ($scale['status'] == 1) {
//                    $data[$scale['id']] = $this->trans(strtoupper($scale['csn']));
//                }
//            }
//        }
//
//        return $data;
//    }
}
