<?php

namespace jobseeker\Bundle\CareerBundle\Controller;

use jobseeker\Bundle\ToolBundle\Base;
use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\CareerBundle\DependencyInjection\JobEncryptInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DeliveryController extends Base
{

    protected $sendCVName = "CareerBundle:Delivery";

    public function deliveryAction()
    {
        $referer = $this->getRequest()->headers->get('referer');
        $jobShowURL = $this->generateurl("job_show", array(), true);
        if ($referer === null || strpos($referer, $jobShowURL) === false) {
            return $this->redirect($this->generateUrl("index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                if (false === parent::autoLogin()) {
                    return $this->render("empty.html.twig", array("status" => "nologin"));
                }

                try {
                    $deliveryArr = $this->decode($this->getRequest()->getContent(), JobEncryptInterface::DELIVERY_SALT);
                    if (!isset($deliveryArr["eme"]) || !isset($deliveryArr["emr"]) || !isset($deliveryArr["email"]) || !isset($deliveryArr["jid"])) {
                        return $this->render("empty.html.twig", array("status" => "invalid"));
                    }

                    $jobShowURL = $this->generateUrl("job_show", array("condition" => $this->encode(array("id" => $deliveryArr['jid']), JobEncryptInterface::SEARCH_CONDITION_SALT)), true);

                    if ($referer === null || strpos($referer, $jobShowURL) === false) {
                        return $this->render("empty.html.twig", array("status" => "invalid"));
                    }

                    if (intval($deliveryArr["eme"]) === intval($deliveryArr["emr"])) {
                        return $this->render("empty.html.twig", array("status" => "same"));
                    }
                } catch (\Exception $e) {
                    return $this->render("empty.html.twig", array("status" => "invalid"));
                }

                $emeEntity = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => intval($deliveryArr["eme"])));
                $eme = $emeEntity->serialize();
                $emrEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => intval($deliveryArr["emr"])));
                $jobEntity = $this->getRepo("CareerBundle:Job")->find(intval($deliveryArr["jid"]));
                $job = $jobEntity->serialize();

                $delivery = $this->getRepo()->findOneBy(array("eme" => $emeEntity->getId(), "emr" => $emrEntity->getId(), "jid" => intval($deliveryArr["jid"])), array("ctime" => "DESC"));

                if (null === $delivery) {
                    try {
                        $deliveryEntity = $this->getEntity();
                        $deliveryEntity->setJid($jobEntity);
                        $deliveryEntity->setEme($emeEntity);
                        $deliveryEntity->setEmail($deliveryArr["email"]);
                        $deliveryEntity->setEmr($emrEntity);
                        $deliveryEntity->setCtime(time());
                        $this->getEm()->persist($deliveryEntity);
                        $this->getEm()->flush();
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "fail"));
                    }

                    $suject = $this->trans("job.delivery.subject", array("%name%" => $eme["name"], "%title%" => $job['title']));
                    $body = $this->renderView("career/delivery/delivery.html.twig", array("eme" => $eme, "job" => $job));
                    $this->sendDeliveryEmail($deliveryArr["email"], $suject, $body);

                    return $this->render("empty.html.twig", array("status" => "success"));
                } else {
                    if (time() <= ($delivery->getCtime() + $this->getSystem("job_expires") ?: 3600 * 24 * 30)) {
                        return $this->render("empty.html.twig", array("status" => "repeat"));
                    } else {
                        try {
                            $deliveryEntity = $this->getEntity();
                            $deliveryEntity->setJid($jobEntity);
                            $deliveryEntity->setEme($emeEntity);
                            $deliveryEntity->setEmail($deliveryArr["email"]);
                            $deliveryEntity->setEmr($emrEntity);
                            $deliveryEntity->setCtime(time());
                            $this->getEm()->persist($deliveryEntity);
                            $this->getEm()->flush();
                        } catch (\Exception $e) {
                            return $this->render("empty.html.twig", array("status" => "fail"));
                        }

                        $suject = $this->trans("job.delivery.subject", array("%name%" => $eme["name"], "%title%" => $job['title']));
                        $body = $this->renderView("career/delivery/delivery.html.twig", array("eme" => $eme, "job" => $job));
                        $this->sendDeliveryEmail($deliveryArr["email"], $suject, $body);

                        return $this->render("empty.html.twig", array("status" => "success"));
                    }
                }
            } else {
                return $this->render("empty.html.twig", array("status" => "invalid"));
            }
        } else {
            return $this->render("empty.html.twig", array("status" => "invalid"));
        }
    }

    public function reserveAction()
    {
        $referer = $this->getRequest()->headers->get('referer');
        $emeShowURL = $this->generateurl("eme_show", array(), true);
        if ($referer === null || strpos($referer, $emeShowURL) === false) {
            return $this->redirect($this->generateUrl("index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                if (false === parent::autoLogin()) {
                    return $this->render("empty.html.twig", array("status" => "nologin"));
                }

                try {
                    $deliveryEncode = json_decode($this->getRequest()->getContent(), TRUE);
                    $deliveryArr = $this->decode($deliveryEncode['data'], JobEncryptInterface::RESERVE_SALT);
                    if (!isset($deliveryArr["emeuid"]) || !isset($deliveryArr["emruid"]) || !isset($deliveryArr["id"])) {
                        return $this->render("empty.html.twig", array("status" => "invalid"));
                    }

                    if (!isset($deliveryEncode['dating']) || count($deliveryEncode['dating']) < 1) {
                        return $this->render("empty.html.twig", array("status" => "less"));
                    }

                    if (!isset($deliveryEncode['dating']) || count($deliveryEncode['dating']) > 5) {
                        return $this->render("empty.html.twig", array("status" => "more"));
                    }

                    foreach ($deliveryEncode['dating'] as $dating) {
                        $reservArr[] = strtotime($dating);
                    }

                    sort($reservArr);

                    $user = $this->getUid();

                    if (intval($user["uid"]) !== intval($deliveryArr["emruid"])) {
                        return $this->render("empty.html.twig", array("status" => "invalid"));
                    }
                } catch (\Exception $e) {
                    return $this->render("empty.html.twig", array("status" => "invalid"));
                }

                $delivery = $this->getRepo()->find(intval($deliveryArr["id"]));

                if (null === $delivery) {
                    return $this->render("empty.html.twig", array("status" => "invalid"));
                } else {
                    if (false !== $delivery->getReserve()) {
                        return $this->render("empty.html.twig", array("status" => "repeat"));
                    } else {
                        try {
                            if (isset($deliveryEncode['dmessage']) && $deliveryEncode['dmessage']) {
                                $delivery->setMessage($deliveryEncode['dmessage']);
                            }
                            $delivery->setReserve($reservArr);
                            $this->getEm()->persist($delivery);
                            $this->getEm()->flush();
                            return $this->render("empty.html.twig", array("status" => "success"));
                        } catch (\Exception $e) {
                            return $this->render("empty.html.twig", array("status" => "fail"));
                        }
                    }
                }
            } else {
                return $this->render("empty.html.twig", array("status" => "invalid"));
            }
        } else {
            return $this->render("empty.html.twig", array("status" => "invalid"));
        }
    }

    public function scheduleAction()
    {
        $referer = $this->getRequest()->headers->get('referer');
        $jobViewURL = $this->generateurl("job_view", array(), true);
        if ($referer === null || strpos($referer, $jobViewURL) === false) {
            return $this->redirect($this->generateUrl("index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                if (false === parent::autoLogin()) {
                    return $this->render("empty.html.twig", array("status" => "nologin"));
                }

                try {
                    $deliveryEncode = json_decode($this->getRequest()->getContent(), TRUE);
                    $deliveryArr = $this->decode($deliveryEncode['data'], JobEncryptInterface::SCHEDULE_SALT);
                    if (!isset($deliveryArr["emeuid"]) || !isset($deliveryArr["emruid"]) || !isset($deliveryArr["id"])) {
                        return $this->render("empty.html.twig", array("status" => "invalid"));
                    }

                    if (!isset($deliveryEncode['schedule']) || strtotime($deliveryEncode['schedule']) === false || strtotime($deliveryEncode['schedule']) === -1) {
                        return $this->render("empty.html.twig", array("status" => "less"));
                    }

                    $user = $this->getUid();

                    if (intval($user["uid"]) !== intval($deliveryArr["emeuid"])) {
                        return $this->render("empty.html.twig", array("status" => "invalid"));
                    }
                } catch (\Exception $e) {
                    return $this->render("empty.html.twig", array("status" => "invalid"));
                }

                $delivery = $this->getRepo()->find(intval($deliveryArr["id"]));

                if (null === $delivery) {
                    return $this->render("empty.html.twig", array("status" => "invalid"));
                } else {
                    if ($delivery->getSchedule()) {
                        return $this->render("empty.html.twig", array("status" => "repeat"));
                    } else {
                        try {
                            $delivery->setSchedule(strtotime($deliveryEncode['schedule']));
                            $this->getEm()->persist($delivery);
                            $this->getEm()->flush();
                            return $this->render("empty.html.twig", array("status" => "success"));
                        } catch (\Exception $e) {
                            return $this->render("empty.html.twig", array("status" => "fail"));
                        }
                    }
                }
            } else {
                return $this->render("empty.html.twig", array("status" => "invalid"));
            }
        } else {
            return $this->render("empty.html.twig", array("status" => "invalid"));
        }
    }

    public function emeAction($opera, $page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        if ("history" === strtolower($opera)) {
            return $this->emeHistory($page);
        } else {
            return $this->emeNew($page);
        }
    }

    public function sendmailAction()
    {

        $referer = $this->getRequest()->headers->get('referer');
        $jobShowURL = $this->generateurl("job_show", array(), true);
        if ($referer === null || strpos($referer, $jobShowURL) === false) {
            return $this->redirect($this->generateUrl("index"));
        }

        $sendCV = $this->getEntity("CareerBundle:SendCV");
        $form = $this->createFormBuilder($sendCV, array("attr" => array("role" => "form")))
            ->setMethod("post")
            ->add('jid', HiddenType::class)
            ->add('emr', HiddenType::class)
            ->add('emailto', HiddenType::class)
            ->add('emailfrom', TextType::class)
            ->add('cv', FileType::class)
            ->add("save", SubmitType::class)
            ->getForm();
        if ($this->isPost()) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $sessionId = $this->getSessionId();
                $employerEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $sendCV->getEmr()));
                $jobEntity = $this->getRepo("CareerBundle:Job")->find($sendCV->getJid());
                $result = $this->getRepo("CareerBundle:SendCV")->isAvailable($jobEntity->getId(), $employerEntity->getId(), $sessionId);
                $result = array_shift($result);
                $joburl = $this->generateUrl("job_show", array(
                    "condition" => $this->getPost("condition")
                ));
                if (is_array($result) && count($result) > 0) {
                    if ((time() - $result["ctime"]) >= 259200) {
                        $mailfrom = $result["emailfrom"] ?: null;
                        $this->sendCV($sendCV->getEmailto(), $joburl, $sendCV->getEmailfrom(), $this->getAttachmentRootPath() . "/" . $result["cv"]);
                        return new JsonResponse(array("success" => true, "uuid" => $this->getPost("uuid")), 200, array("Content-Type" => "text/plain"));
                    } else {
                        return new JsonResponse(array("success" => false, "error" => $this->trans("job.sendcv.expires"), "preventRetry" => true), 200, array("Content-Type" => "text/plain"));
                    }
                } else {
                    if ($sendCV->getCv()) {
                        $sendCV->setJid($jobEntity);
                        $sendCV->setEmr($employerEntity);
                        $sendCV->setSessionid($sessionId);
                        $sendCV->upload($this, "cv", $this->getAttachmentRootPath());
                        $sendCV->setCtime(time());
                        $this->getEm()->persist($sendCV);
                        $this->getEm()->flush();
                        $this->sendCV($sendCV->getEmailto(), $joburl, $sendCV->getEmailfrom(), $this->getAttachmentRootPath() . "/" . $sendCV->getCv());
                        return new JsonResponse(array("success" => true, "uuid" => $this->getPost("uuid")), 200, array("Content-Type" => "text/plain"));
                    } else {
                        return new JsonResponse(array("success" => false, "error" => $this->trans("job.sendcv.notupload"), "preventRetry" => true), 200, array("Content-Type" => "text/plain"));
                    }
                }
            } else {
                return new JsonResponse(array("success" => false, "error" => $this->trans("job.sendcv.error"), "preventRetry" => true), 200, array("Content-Type" => "text/plain"));
            }
        }
    }

    private function emeNew($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $deliveries = array();
        $renderValue = array();
        $user = $this->getUid();
        $employeeEntity = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
        if (null !== $employeeEntity) {
            $offset = $this->getPerPage();
            $pager = $this->generatePager("/eme/delivery-new-%d", $page, $offset, $this->getRepo()->getTotalByEme($employeeEntity->getId()));
            $deliveries = $this->getRepo()->getAllForPagerByEme($employeeEntity->getId(), 1, $page, $offset);
            if (count($deliveries) > 0) {
                foreach ($deliveries as $key => $delivery) {
                    $deliveries[$key]["condition"] = $this->encode(array("id" => intval($delivery["did"]), "jid" => intval($delivery["jid"]), "emeuid" => intval($delivery["emeuid"]), "emruid" => intval($delivery["emruid"])), JobEncryptInterface::JOB_VIEW_SALT);
                }
                $renderValue['deliveries'] = $deliveries;
                $renderValue['pages'] = $pager->generateUrl();
            }
        }

        return $this->render("career/delivery/eme_new.html.twig", $renderValue);
    }

    private function emeHistory($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $deliveries = array();
        $renderValue = array();
        $user = $this->getUid();
        $employeeEntity = $this->getRepo("UserBundle:Employee")->findOneBy(array("uid" => $user['uid']));
        if (null !== $employeeEntity) {
            $offset = $this->getPerPage();
            $pager = $this->generatePager("/eme/delivery-history-%d", $page, $offset, $this->getRepo()->getTotalByEme($employeeEntity->getId(), 1));
            $deliveries = $this->getRepo()->getAllForPagerByEme($employeeEntity->getId(), 0, $page, $offset);
            if (count($deliveries) > 0) {
                foreach ($deliveries as $key => $delivery) {
                    $deliveries[$key]["condition"] = $this->encode(array("id" => intval($delivery["did"]), "jid" => intval($delivery["jid"]), "emeuid" => intval($delivery["emeuid"]), "emruid" => intval($delivery["emruid"])), JobEncryptInterface::JOB_VIEW_SALT);
                }
                $renderValue['deliveries'] = $deliveries;
                $renderValue['pages'] = $pager->generateUrl();
            }
        }

        return $this->render("career/delivery/eme_history.html.twig", $renderValue);
    }

    public function emrAction($opera, $page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        if ("history" === strtolower($opera)) {
            return $this->emrHistory($page);
        } else {
            return $this->emrNew($page);
        }
    }

    private function emrNew($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $deliveries = array();
        $renderValue = array();
        $user = $this->getUid();
        $employerEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $user['uid']));
        if (null !== $employerEntity) {
            $offset = $this->getPerPage();
            $pager = $this->generatePager("/emr/delivery-new-%d", $page, $offset, $this->getRepo()->getTotalByEmr($employerEntity->getId()));
            $deliveries = $this->getRepo()->getAllForPagerByEmr($employerEntity->getId(), 0, $page, $offset);
            if (count($deliveries) > 0) {
                foreach ($deliveries as $key => $delivery) {
                    $deliveries[$key]["condition"] = $this->encode(array("emeuid" => intval($delivery["eme"]['uid']), "id" => intval($delivery["id"]), "emruid" => intval($delivery["emr"]['uid'])), JobEncryptInterface::EMPLOYEE_SHOW_SALT);
                }
                $renderValue['deliveries'] = $deliveries;
                $renderValue['pages'] = $pager->generateUrl();
            }
        }

        return $this->render("career/delivery/emr_new.html.twig", $renderValue);
    }

    private function emrHistory($page)
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $deliveries = array();
        $renderValue = array();
        $user = $this->getUid();
        $employerEntity = $this->getRepo("UserBundle:Employer")->findOneBy(array("uid" => $user['uid']));
        if (null !== $employerEntity) {
            $offset = $this->getPerPage();
            $pager = $this->generatePager("/emr/delivery-history-%d", $page, $offset, $this->getRepo()->getTotalByEmr($employerEntity->getId(), 1));
            $deliveries = $this->getRepo()->getAllForPagerByEmr($employerEntity->getId(), 1, $page, $offset);
            if (count($deliveries) > 0) {
                foreach ($deliveries as $key => $delivery) {
                    $deliveries[$key]["condition"] = $this->encode(array("emeuid" => intval($delivery["eme"]['uid']), "id" => intval($delivery["id"]), "emruid" => intval($delivery["emr"]['uid'])), JobEncryptInterface::EMPLOYEE_SHOW_SALT);
                }
                $renderValue['deliveries'] = $deliveries;
                $renderValue['pages'] = $pager->generateUrl();
            }
        }

        return $this->render("career/delivery/emr_history.html.twig", $renderValue);
    }

}
