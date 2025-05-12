<?php

namespace jobseeker\Bundle\UserBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use jobseeker\Bundle\ToolBundle\Base;

class ExperienceController extends Base
{

    protected $entityName = "UserBundle:Experience";

    public function indexAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $user = $this->getUid();

        $experiences = $this->getRepo()->findBy(array("uid" => $user['uid']), array("orientation" => "DESC"));

        if (count($experiences) == 0) {
            $experience = $this->getEntity();
            $experience->setUid($user['uid']);
            $experiences[] = $experience;
        }

        foreach ($experiences as $experience) {
            $forms[] = $this->createFormBuilder($experience, array("attr" => array("role" => "form")))
                            ->setAction($this->generateUrl("account_exp_set"))
                            ->setMethod("post")
                            ->add("id", HiddenType::class, array("data" => $experience->getId()))
                            ->add('location', TextType::class, array("required" => true, "data" => $experience->getLocation(), "attr" => array("class" => "form-control input-sm")))
                            ->add('company', TextType::class, array("required" => true, "data" => $experience->getCompany(), "attr" => array("class" => "form-control input-sm")))
                            ->add('orientation', TextType::class, array(
                                "required" => true,
                                "attr" => array(
                                    "class" => "form-control input-sm input-date datetime",
                                    "data-date-format" => $this->convertDateFormatForJS(),
                                    "startDate" => date($this->getSystem("date_format"), time() - 3600 * 24 * 365 * 100),
                                ),
                                "data" => $experience->getOrientation() ? date($this->getSystem("date_format"), $experience->getOrientation()) : date($this->getSystem("date_format"), time() - 3600 * 24 * 365)
                            ))
                            ->add('dimission', TextType::class, array(
                                "required" => true,
                                "attr" => array(
                                    "class" => "form-control input-sm input-date datetime",
                                    "data-date-format" => $this->convertDateFormatForJS(),
                                    "startDate" => date($this->getSystem("date_format"), time() - 3600 * 24 * 365 * 100),
                                ),
                                "data" => $experience->getDimission() ? date($this->getSystem("date_format"), $experience->getDimission()) : date($this->getSystem("date_format"), time() - 3600 * 24 * 30)
                            ))
                            ->add('title', TextType::class, array("required" => true, "data" => $experience->getTitle(), "attr" => array("class" => "form-control input-sm")))
                            ->add('description', TextareaType::class, array("required" => true, "data" => $experience->getDescription(), "attr" => array("class" => "exp_description form-control input-sm")))
                            ->getForm()->createView();
        }

        $renderValue = array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "forms" => $forms
        );

        return $this->render('user/experience/index.html.twig', $renderValue);
    }

    public function saveAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $user = $this->getUid();

        $oldentity = null;

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $experience = json_decode($this->getRequest()->getContent(), true);
                if (isset($experience['id']) && $experience['id']) {
                    $entity = $this->getRepo()->findOneBy(array("id" => (int) $experience['id'], "uid" => $user['uid']));
                    if (null !== $entity) {
                        $oldentity = clone $entity;
                    } else {
                        $entity = $this->getEntity();
                        $entity->setUid($user['uid']);
                    }
                } else {
                    $entity = $this->getEntity();
                    $entity->setUid($user['uid']);
                }
            } else {
                $experience = $this->getFormData();
                $entity = $this->getEntity();
                $entity->setUid($user['uid']);
            }
            try {
                foreach (array("location", "company", "orientation", "dimission", "title", "description") as $requiredColumn) {
                    if (!isset($experience[$requiredColumn]) || !$experience[$requiredColumn]) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
                $entity->setLocation($experience['location']);
                $entity->setCompany($experience['company']);
                $entity->setOrientation($experience['orientation']);
                $entity->setDimission($experience['dimission']);
                $entity->setTitle($experience['title']);
                $entity->setDescription($experience['description']);
            } catch (\Exception $e) {
                return $this->render("empty.html.twig", array("status" => "error"));
            }

            if ($this->isAjax()) {
                if (null !== $oldentity && $oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->persist($entity);
                        $this->getEm()->flush();
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
            } else {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                return $this->redirect($this->generateUrl("account_exp"));
            }
        } else {
            return $this->redirect($this->generateUrl("account_exp"));
        }
    }

    public function deleteAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $user = $this->getUid();

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $id = $this->getRequest()->getContent();
                $entity = $this->getRepo()->findOneBy(array("id" => $id, "uid" => $user['uid']));
                if (null !== $entity) {
                    try {
                        $this->getEm()->remove($entity);
                        $this->getEm()->flush();
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                } else {
                    return $this->render("empty.html.twig", array("status" => "error"));
                }
            }
        } else {
            return $this->redirect($this->generateUrl("account_exp"));
        }
    }

}
