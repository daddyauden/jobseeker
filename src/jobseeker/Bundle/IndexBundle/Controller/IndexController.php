<?php

namespace jobseeker\Bundle\IndexBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use jobseeker\Bundle\ToolBundle\Base;
use jobseeker\Bundle\CareerBundle\DependencyInjection\JobEncryptInterface;

class IndexController extends Base
{

    public function indexAction()
    {
        $renderValue = array();

        $renderValue["isLogin"] = false;

        $user = $this->getUid();

        if (NULL !== $user) {
            $employeeEntity = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
            $employerEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $user['uid']));
            if (NULL !== $employerEntity) {
                $renderValue['eme_or_emr'] = $employerEntity->serialize();
                $this->addEmployer($user);
            } else if (NULL !== $employeeEntity) {
                $renderValue['eme_or_emr'] = $employeeEntity->serialize();
                $this->delEmployer($user);
            }
            if (true === parent::autoLogin()) {
                $renderValue["isLogin"] = true;
            }
        }

        $entity = $this->getEntity("CareerBundle:Job");

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("search"))
            ->setMethod("post")
            ->add('industry', HiddenType::class)
            ->add('area', HiddenType::class)
            ->add('type', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => $this->getTypeForSearch(),
                'placeholder' => $this->trans("job.search.type"),
            ))
            ->add('salary', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => array(
                    $this->trans('common.lt') . " 1000" => "<1000",
                    "1000-2000" => "1000-2000",
                    "2001-3000" => "2001-3000",
                    "3001-5000" => "3001-5000",
                    "5001-8000" => "5001-8000",
                    $this->trans("common.gt") . " 8000" => ">8000"
                ),
                'placeholder' => $this->trans("job.search.salary"),
            ))
            ->add('begintime', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => array(
                    $this->trans("common.recent") . " 7 " . $this->trans("common.date.day") => ">=604800",
                    $this->trans("common.recent") . " 15 " . $this->trans("common.date.day") => ">=1296000",
                    "1 " . $this->trans("common.date.month") . " " . $this->trans("common.ago") => "<=2592000",
                    "2 " . $this->trans("common.date.month") . " " . $this->trans("common.ago") => "<=5184000",
                    "3 " . $this->trans("common.date.month") . " " . $this->trans("common.ago") => "<=7776000",
                    $this->trans("common.date.half_year") . " " . $this->trans("common.ago") => "<=15552000"
                ),
                'placeholder' => $this->trans("job.search.begintime"),
            ))
            ->add("search", SubmitType::class, array(
                "label" => $this->trans("common.search"),
                "attr" => array("class" => "select-search")
            ))
            ->getForm();

        $renderValue = array_merge($renderValue, array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "brand_intro" => $this->brandIntro(),
            "form" => $form->createView(),
            "industry" => $this->getIndustryForSearch(),
            "area" => $this->getAreaForSearch(),
        ));

        return $this->render("index/index/index.html.twig", $renderValue);
    }

    public function searchAction()
    {
        $renderValue = array();

        $renderValue["isLogin"] = false;

        $user = $this->getUid();

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
        }

        $entity = $this->getEntity("CareerBundle:Job");

        $form = $this->createFormBuilder($entity, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("search"))
            ->setMethod("post")
            ->add('industry', HiddenType::class)
            ->add('area', HiddenType::class)
            ->add('type', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => $this->getTypeForSearch(),
                'placeholder' => $this->trans("job.search.type"),
            ))
            ->add('salary', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => array(
                    $this->trans('common.lt') . " 1000" => "<1000",
                    "1000-2000" => "1000-2000",
                    "2001-3000" => "2001-3000",
                    "3001-5000" => "3001-5000",
                    "5001-8000" => "5001-8000",
                    $this->trans("common.gt") . " 8000" => ">8000"
                ),
                'placeholder' => $this->trans("job.search.salary"),
            ))
            ->add('begintime', ChoiceType::class, array(
                "attr" => array("class" => "form-control"),
                "required" => false,
                "multiple" => false,
                "expanded" => false,
                "choices" => array(
                    $this->trans("common.recent") . " 7 " . $this->trans("common.date.day") => ">=604800",
                    $this->trans("common.recent") . " 15 " . $this->trans("common.date.day") => ">=1296000",
                    "1 " . $this->trans("common.date.month") . " " . $this->trans("common.ago") => "<=2592000",
                    "2 " . $this->trans("common.date.month") . " " . $this->trans("common.ago") => "<=5184000",
                    "3 " . $this->trans("common.date.month") . " " . $this->trans("common.ago") => "<=7776000",
                    $this->trans("common.date.half_year") . " " . $this->trans("common.ago") => "<=15552000"
                ),
                'placeholder' => $this->trans("job.search.begintime"),
            ))
            ->add("search", SubmitType::class, array(
                "label" => $this->trans("common.search"),
                "attr" => array("class" => "select-search")
            ))
            ->getForm();

        $renderValue = array_merge($renderValue, array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "form" => $form->createView(),
            "industry" => $this->getIndustryForSearch(),
            "area" => $this->getAreaForSearch()
        ));

        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid() && $form->isSubmitted()) {
                $condition = array();
                foreach (array("type", "industry", "area", "salary", "begintime") as $key) {
                    $name = "get" . ucfirst($key);
                    if (NULL !== ${$key} = $entity->$name()) {
                        if ($key == "industry") {
                            $value = explode(",", ${$key});
                            $ids = array_map(function ($id) {
                                return intval($id);
                            }, $value);
                            $condition[$key] = $ids;
                            $industries = $this->getCache("CareerBundle:Industry");
                            $pid = (int)$industries[$condition[$key][0]]['pid'];
                            $renderValue[$key . "Default"] = array($pid, $condition[$key]);
                        } else if ($key == "area") {
                            $value = explode(",", ${$key});
                            $ids = array_map(function ($id) {
                                return intval($id);
                            }, $value);
                            $condition[$key] = $ids;
                            $areas = $this->getCache("AdminBundle:Area");
                            $pid = (int)$areas[$condition[$key][0]]['pid'];
                            $renderValue[$key . "Default"] = array($pid, $condition[$key]);
                        } else {
                            $condition[$key] = ${$key};
                            $renderValue[$key . "Default"] = ${$key};
                        }
                    }
                }

                // when user dont choose any condition, return none data
//                if (count($condition) > 0) {
//                    $job_list = $this->generateUrl("job_search", array("condition" => $this->encode($condition, JobEncryptInterface::SEARCH_CONDITION_SALT)));
//                    $renderValue['job_list'] = $job_list;
//                } else {
//                    $this->get("session")->getFlashBag()->add("info", $this->trans("job.search.condition"));
//                }
                // when user dont choose any condition, return all data
                $job_list = $this->generateUrl("job_search", array("condition" => $this->encode($condition, JobEncryptInterface::SEARCH_CONDITION_SALT)));
                $renderValue['job_list'] = $job_list;

                return $this->render("index/index/search.html.twig", $renderValue);
            }
        }

        return $this->render("index/index/search.html.twig", $renderValue);
    }

    public function brandAction()
    {
        return $this->render('index/index/brand.html.twig', array("brand_intro" => $this->brandIntro()));
    }

    public function dashboardAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $renderValue = array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale()
        );

        $routeArr = array("account_eme", "account_edu", "account_exp", "account_emr", "job_list", "eme_delivery", "emr_delivery");

        if (false !== $this->hasQuery("q")) {
            $q = strtolower($this->getQuery("q"));
            if (in_array($q, $routeArr)) {
                $renderValue['route'] = $this->generateUrl($q);
            }
        }

        $user = $this->getUid();

        if (NULL !== $user) {
            $employeeEntity = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
            $employerEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $user['uid']));
            if (NULL !== $employeeEntity) {
                $renderValue['eme_or_emr'] = array("avator" => $employeeEntity->getAvator());
            } else if (NULL !== $employerEntity) {
                $renderValue['eme_or_emr'] = array("avator" => $employerEntity->getAvator());
            } else {

            }
        }

        $renderValue["isEmployer"] = $this->isEmployer($user) ? 1 : 0;

        return $this->render('index/index/dashboard.html.twig', $renderValue);
    }

    final public function dumpCache()
    {
        parent::dumpCache();

        $industry = array();
        $industryEntity = $this->getRepo("CareerBundle:Industry")->getAll();
        if (count($industryEntity) > 0) {
            foreach ($industryEntity as $value) {
                $industry[$value['id']] = $value;
                if ($value['pid'] != 0) {
                    $industry[$value['pid']]['child'][$value['id']] = $value;
                }
            }
        }
        parent::buildCache($industry, "CareerBundle:Industry");
    }

}
