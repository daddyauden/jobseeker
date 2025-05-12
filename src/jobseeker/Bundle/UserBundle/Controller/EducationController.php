<?php

namespace jobseeker\Bundle\UserBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use jobseeker\Bundle\ToolBundle\Base;

class EducationController extends Base
{

    protected $entityName = "UserBundle:Education";

    public function indexAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("index"));
        }

        $user = $this->getUid();

        $educations = $this->getRepo()->findBy(array("uid" => $user['uid']), array("graduation" => "DESC"));

        if (count($educations) == 0) {
            $education = $this->getEntity();
            $education->setUid($user['uid']);
            $educations[] = $education;
        }

        foreach ($educations as $education) {
            $forms[] = $this->createFormBuilder($education, array("attr" => array("role" => "form")))
                ->setAction($this->generateUrl("account_edu_set"))
                ->setMethod("post")
                ->add("id", HiddenType::class, array("data" => $education->getId()))
                ->add('university', TextType::class, array("required" => true, "data" => $education->getUniversity(), "attr" => array("class" => "form-control input-sm")))
                ->add('graduation', TextType::class, array(
                    "required" => true,
                    "attr" => array(
                        "class" => "form-control input-sm input-date datetime",
                        "data-date-format" => $this->convertDateFormatForJS(),
                        "startDate" => date($this->getSystem("date_format"), time() - 3600 * 24 * 365 * 100),
                    ),
                    "data" => $education->getGraduation() ? date($this->getSystem("date_format"), $education->getGraduation()) : date($this->getSystem("date_format"), time() - 3600 * 24 * 90)
                ))
                ->add('diploma', ChoiceType::class, array(
                    "attr" => array("class" => "form-control input-sm"),
                    "required" => true,
                    "choices" => $this->getDiplomaForChoices(),
                    "multiple" => false,
                    'expanded' => false,
                    'data' => $education->getDiploma() === null ? "" : $education->getDiploma()->getId()
                ))
                ->add('major', TextType::class, array("required" => true, "data" => $education->getMajor(), "attr" => array("class" => "form-control input-sm")))
                ->add('course', TextType::class, array("required" => true, "data" => $education->getCourse(), "attr" => array("class" => "form-control input-sm")))
                ->add('description', TextareaType::class, array("required" => false, "data" => $education->getDescription(), "attr" => array("class" => "form-control input-sm edu_description")))
                ->getForm()->createView();
        }

        $renderValue = array(
            "areas" => $this->getArea(),
            "locales" => $this->getLocale(),
            "forms" => $forms
        );

        return $this->render('user/education/index.html.twig', $renderValue);
    }

    private function getDiplomaForChoices()
    {
        $diplomaChoices = array($this->trans("common.choose") => 0);
        $diplomas = $this->getRepo("UserBundle:Category")->getAvailable("employee_diploma");
        if (count($diplomas) > 0) {
            foreach ($diplomas as $diploma) {
                if ($diploma["pid"] == 0 && $diploma['status'] == 1) {
                    $diplomaChoices[$this->trans($diploma['csn'])] = $diploma['id'];
                }
            }
        }

        return $diplomaChoices;
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
                $education = json_decode($this->getRequest()->getContent(), true);
                if (isset($education['id']) && $education['id']) {
                    $entity = $this->getRepo()->findOneBy(array("id" => (int)$education['id'], "uid" => $user['uid']));
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
                $education = $this->getFormData();
                $entity = $this->getEntity();
                $entity->setUid($user['uid']);
            }
            try {
                foreach (array("university", "graduation", "diploma", "major", "course") as $requiredColumn) {
                    if (!isset($education[$requiredColumn]) || !$education[$requiredColumn]) {
                        return $this->render("empty.html.twig", array("status" => "error"));
                    }
                }
                $entity->setUniversity($education['university']);
                $entity->setGraduation($education['graduation']);
                $diploma = $this->getRepo("UserBundle:Category")->find((int)$education['diploma']);
                if (null !== $diploma) {
                    $entity->setDiploma($diploma);
                }
                $entity->setMajor($education['major']);
                $entity->setCourse($education['course']);
                if (isset($education['description'])) {
                    $entity->setDescription($education['description']);
                }
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
                return $this->redirect($this->generateUrl("account_edu"));
            }
        } else {
            return $this->redirect($this->generateUrl("account_edu"));
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
            return $this->redirect($this->generateUrl("account_edu"));
        }
    }

}
