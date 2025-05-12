<?php

namespace jobseeker\Bundle\UserBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use jobseeker\Bundle\ToolBundle\Base;
use jobseeker\Bundle\CareerBundle\DependencyInjection\JobEncryptInterface;

class EmployeeController extends Base
{

    protected $entityName = "UserBundle:Employee";

    public function indexAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $user = $this->getUid();

        $employee = $this->getRepo()->findOneBy(array("uid" => $user['uid']));

        if (null === $employee) {
            $employee = $this->getEntity();
            $employee->setUid($user['uid']);
        }

        $oldEmployee = clone $employee;

        $form = $this->createFormBuilder($employee, array("attr" => array("role" => "form")))
            ->setAction($this->generateUrl("account_eme"))
            ->setMethod("post")
            ->add('name', TextType::class, array("required" => true, "data" => $employee->getName(), "attr" => array("class" => "form-control input-sm")))
            ->add('gender', ChoiceType::class, array(
                "choices" => array(
                    $this->trans("table.employee.gender.0") => 0,
                    $this->trans("table.employee.gender.1") => 1
                ),
                "multiple" => false,
                'required' => true,
                'expanded' => true,
                'data' => $employee->getGender()
            ))
            ->add('marital', ChoiceType::class, array(
                "choices" => array(
                    $this->trans("table.employee.marital.0") => 0,
                    $this->trans("table.employee.marital.1") => 1
                ),
                "multiple" => false,
                'required' => true,
                'expanded' => true,
                'data' => $employee->getMarital()
            ))
            ->add('birthday', TextType::class, array(
                'required' => false,
                "attr" => array(
                    "class" => "form-control input-sm input-date datetime",
                    "data-date-format" => $this->convertDateFormatForJS(),
                    "startdate" => date($this->getSystem("date_format"), time() - 3600 * 24 * 365 * 100),
                ),
                "data" => $employee->getBirthday() ? date($this->getSystem("date_format"), $employee->getBirthday()) : date($this->getSystem("date_format"), time() - 3600 * 24 * 365 * 30)
            ))
            ->add('nationality', TextType::class, array("required" => true, "data" => $employee->getNationality(), "attr" => array("class" => "form-control input-sm")))
            ->add('hometown', TextType::class, array("required" => true, "data" => $employee->getHometown(), "attr" => array("class" => "form-control input-sm")))
            ->add('location', TextType::class, array("required" => true, "data" => $employee->getLocation(), "attr" => array("class" => "form-control input-sm")))
            ->add('mobile', TextType::class, array("required" => true, "data" => $employee->getMobile(), "attr" => array("class" => "form-control input-sm")))
            ->add('email', TextType::class, array("required" => true, "data" => $employee->getEmail(), "attr" => array("class" => "form-control input-sm")))
            ->add('interest', TextareaType::class, array("required" => false, "data" => $employee->getInterest(), "attr" => array("class" => "form-control input-sm")))
            ->add('skill', TextareaType::class, array("required" => false, "data" => $employee->getSkill(), "attr" => array("class" => "form-control input-sm")))
            ->add('language', TextareaType::class, array("required" => false, "data" => $employee->getLanguage(), "attr" => array("class" => "form-control input-sm")))
            ->add('description', TextareaType::class, array("required" => false, "data" => $employee->getDescription(), "attr" => array("class" => "form-control input-sm")))
            ->add("save", SubmitType::class, array("label" => $this->trans("common.save"), "attr" => array("class" => "btn btn-sm btn-primary col-2 col-offset-3")))
            ->getForm();

        if (null === $employee->getId()) {
            $form->add('avator', FileType::class, array(
                "required" => true,
            ));
        } else {
            $form->add('avator', FileType::class, array(
                "required" => $employee->getAvator() ? false : true,
                "data" => $employee->getAvator() ? $employee->getView($this, "avator") : null
            ));
        }

        if (null !== $employee->getId()) {
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

                if (null === $employee->getId()) {
                    $employee->upload($this, "avator", $this->getAvatorRootPath());
                } else {
                    if (null === $employee->getAvator()) {
                        $employee->setAvator($oldEmployee->getAvator() ?: null);
                    } else {
                        $status = $employee->upload($this, "avator", $this->getAvatorRootPath());
                        if ($status === 1) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_type"));
                            return $this->redirect($this->generateUrl("account_eme"));
                        } else if ($status === 2) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_size"));
                            return $this->redirect($this->generateUrl("account_eme"));
                        } else if ($status === 3) {
                            $this->get("session")->getFlashBag()->add("danger", $this->trans("common.upload_error_other"));
                            return $this->redirect($this->generateUrl("account_eme"));
                        } else {
                            if ($tmp = $oldEmployee->getAvator()) {
                                $this->deleteAvator($tmp);
                            }
                        }
                    }
                }

                $this->getEm()->persist($employee);
                $this->getEm()->flush();
                $this->get("session")->getFlashBag()->add("info", $this->trans("be.update.success"));
                return $this->redirect($this->generateUrl("account_eme"));
            }

            if ($form->isValid() && $form->get("delete")->isClicked()) {
                try {
                    $this->getEm()->remove($employee);
                    $this->getEm()->flush();
                    $this->get("session")->getFlashBag()->add("info", $this->trans("be.delete.success"));
                    return $this->redirect($this->generateUrl("account_eme"));
                } catch (\Exception $e) {
                    $this->get("session")->getFlashBag()->add("info", $this->trans("be.delete.fail"));
                    return $this->redirect($this->generateUrl("account_eme"));
                }
            }
        }

        return $this->render('user/employee/index.html.twig', $renderValue);
    }

    public function showAction($condition)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $user = $this->getUid();

        $referer = $this->getRequest()->headers->get('referer');
        $emrDeliveryURL = $this->generateurl("emr_delivery", array(), true);
        if ($referer === null || strpos($referer, $emrDeliveryURL) === false) {
            return $this->redirect($this->generateurl("index"));
        } else {
            $rendValue = array();
            $reserveArr = array();
            $delivery = $this->decode($condition, JobEncryptInterface::EMPLOYEE_SHOW_SALT);

            $employeeEntity = null;

            if (isset($delivery['emeuid'])) {
                $employeeEntity = $this->getRepo()->findOneBy(array("uid" => $delivery['emeuid']));
                $reserveArr['emeuid'] = intval($delivery['emeuid']);
            }

            if (null !== $employeeEntity) {
                if (isset($delivery['id']) && isset($delivery['emruid'])) {
                    $reserveArr['emruid'] = intval($delivery['emruid']);
                    $reserveArr['id'] = intval($delivery['id']);
                    $deliveryEntity = $this->getRepo("CareerBundle:Delivery")->find($delivery['id']);
                    if (intval($delivery['emruid']) === $user['uid'] && null !== $deliveryEntity && intval($deliveryEntity->getReaded()) === 0) {
                        $deliveryEntity->setReaded(1);
                        $this->getEm()->persist($deliveryEntity);
                        $this->getEm()->flush();
                    }

                    if (!$deliveryEntity->getReserve() && count($reserveArr) > 0) {
                        $rendValue["reserve"] = $this->encode($reserveArr, JobEncryptInterface::RESERVE_SALT);
                    }

                    if ($deliveryEntity->getReserve() && count($deliveryEntity->getReserve()) > 0) {
                        $rendValue["reserved"]["reserve"] = $deliveryEntity->getReserve();
                        if ($deliveryEntity->getSchedule()) {
                            $rendValue["reserved"]["schedule"] = $deliveryEntity->getSchedule();
                        }
                        if ($deliveryEntity->getMessage()) {
                            $rendValue["reserved"]["message"] = $deliveryEntity->getMessage();
                        }
                    }
                }

                $employee = $employeeEntity->serialize();
                $rendValue["eme"] = $employee;

                $experiences = $this->getRepo("UserBundle:Experience")->getExpBy(array("uid", $delivery['emeuid']));
                if (count($experiences) > 0) {
                    $rendValue['exps'] = $experiences;
                }

                $educations = $this->getRepo("UserBundle:Education")->getEduBy(array("uid", $delivery['emeuid']));
                if (count($educations) > 0) {
                    $rendValue['edus'] = $educations;
                }
            } else {
                $this->get("session")->getFlashBag()->add("danger", "Employee Not Exists");
            }

            return $this->render('user/employee/show.html.twig', $rendValue);
        }
    }

}
