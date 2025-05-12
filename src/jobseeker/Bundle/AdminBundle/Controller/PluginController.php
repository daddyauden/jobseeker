<?php

namespace jobseeker\Bundle\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use jobseeker\Bundle\AdminBundle\AdminBase;
use jobseeker\Bundle\PluginBundle\DependencyInjection\AbstractScope;

class PluginController extends AdminBase
{

    protected $entityName = "PluginBundle:Plugin";

    public function installedAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $forms = array();
        $installed = $this->getInstalledPlugins();
        if (count($installed) > 0) {
            foreach ($installed as $scope => $plugins) {
                if (count($plugins) > 0) {
                    foreach ($plugins as $pluginName => $plugin) {
                        $form = $this->createFormBuilder(null, array("attr" => array("role" => "form", "class" => "form")))
                            ->add("id", HiddenType::class, array("data" => $plugin['id']))
                            ->add("logo", TextType::class, array("data" => $plugin['logo']))
                            ->add("namealias", TextType::class, array("label" => $this->trans("plugin.name." . $plugin['name']), "attr" => array("class" => "form-control input-sm")))
                            ->add("description", TextType::class, array("label" => $this->trans("plugin.description." . $plugin['name']), "attr" => array("class" => "form-control input-sm")))
                            ->add("status", ChoiceType::class, array(
                                "label" => $this->trans("table.plugin.status"),
                                "attr" => array(
                                    "class" => "form-control input-sm"
                                ),
                                "choices" => array(
                                    $this->trans("table.plugin.status.1") => 1,
                                    $this->trans("table.plugin.status.0") => 0
                                ),
                                "multiple" => false,
                                'required' => true,
                                'expanded' => false,
                                'data' => (int)$plugin['status']
                            ));
                        $config = $this->assembleConfig(unserialize($plugin['config']));
                        if (count($config) > 0) {
                            foreach ($config as $key => $value) {
                                if ("config_" === $prefix = substr($key, 0, 7)) {
                                    $label = substr($key, 7);
                                } else {
                                    $label = $key;
                                }
                                $form->add($key, TextType::class, array("data" => trim($value), "required" => true, "label" => $label, "attr" => array("class" => "form-control")));
                            }
                        }
                        $forms[$scope][] = $form->getForm()->createView();
                    }
                }
            }
        }

        return $this->render("admin/plugin/installed.html.twig", array("pluginss" => $forms));
    }

    public function uninstalledAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        $forms = array();
        $uninstalled = $this->getUninstalledPlugins();

        if (count($uninstalled) > 0) {
            foreach ($uninstalled as $scope => $plugins) {
                if (count($plugins) > 0) {
                    foreach ($plugins as $pluginName => $plugin) {
                        $form = $this->createFormBuilder(null, array("attr" => array("role" => "form", "class" => "form")))
                            ->add("name", HiddenType::class, array("data" => $pluginName))
                            ->add("scope", HiddenType::class, array("data" => $scope))
                            ->add("status", HiddenType::class, array("data" => 0))
                            ->add("logo", TextType::class, array("data" => $plugin->getLogo()))
                            ->add("namealias", TextType::class, array("label" => $this->trans("plugin.name." . $pluginName), "attr" => array("class" => "form-control input-sm")))
                            ->add("description", TextType::class, array("label" => $this->trans("plugin.description." . $pluginName), "attr" => array("class" => "form-control input-sm")));
                        $config = $this->assembleConfig($this->get($plugin->getName() . 'plugin')->getConfigForInstall());
                        if (count($config) > 0) {
                            foreach ($config as $key => $value) {
                                if ("config_" === $prefix = substr($key, 0, 7)) {
                                    $label = substr($key, 7);
                                } else {
                                    $label = $key;
                                }
                                $form->add($key, TextType::class, array("data" => trim($value), "required" => true, "label" => $label, "attr" => array("class" => "form-control input-sm")));
                            }
                        }
                        $forms[$scope][] = $form->getForm()->createView();
                    }
                }
            }
        }

        return $this->render("admin/plugin/uninstalled.html.twig", array("pluginss" => $forms));
    }

    public function installAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $data = json_decode($this->getRequest()->getContent(), true);
                $name = $data['name'];
                $entity = $this->getRepo($this->getEntityName())->findOneBy(array("name" => $name));
                if ($entity) {
                    return $this->render("empty.html.twig", array("status" => "installed"));
                } else {
                    $entity = $this->getEntity();
                }
            } else {
                $data = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setName($data['name']);
            $entity->setScope($data['scope']);
            $entity->setStatus((int)$data['status']);
            $entity->setConfig(serialize($this->disassembleConfig($data)));
            try {
                $this->getEm()->persist($entity);
                $this->getEm()->flush();
                $this->dumpCache();
                return $this->render("empty.html.twig", array("status" => "success"));
            } catch (\Exception $e) {
                return $this->render("empty.html.twig", array("status" => "fail"));
            }
        } else {
            return $this->render("empty.html.twig", array("status" => "fail"));
        }
    }

    public function uninstallAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $id = $this->getRequest()->getContent();
                $entity = $this->getRepo($this->getEntityName())->find($id);
                if ($entity) {
                    try {
                        $this->getEm()->remove($entity);
                        $this->getEm()->flush();
                        $this->dumpCache();
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "fail"));
                    }
                } else {
                    return $this->render("empty.html.twig", array("status" => "uninstalled"));
                }
            } else {
                return $this->render("empty.html.twig", array("status" => "fail"));
            }
        } else {
            return $this->render("empty.html.twig", array("status" => "fail"));
        }
    }

    public function updateAction()
    {
        if (false === parent::autoLogin()) {
            return $this->redirect($this->generateUrl("admin_index"));
        }

        if ($this->isPost()) {
            if ($this->isAjax()) {
                $data = json_decode($this->getRequest()->getContent(), true);
                $id = $data['id'];
                $entity = $this->getRepo($this->getEntityName())->find($id);
                if (!$entity) {
                    return $this->render("empty.html.twig", array("status" => "invalid"));
                } else {
                    $oldentity = clone $entity;
                }
            } else {
                $data = $this->getFormData();
                $entity = $this->getEntity();
            }
            $entity->setStatus((int)$data['status']);
            $entity->setConfig(serialize($this->disassembleConfig($data)));
            if ($this->isAjax()) {
                if (isset($oldentity) && $oldentity == $entity) {
                    return $this->render("empty.html.twig", array("status" => "same"));
                } else {
                    try {
                        $this->getEm()->flush();
                        $this->dumpCache();
                        return $this->render("empty.html.twig", array("status" => "success"));
                    } catch (\Exception $e) {
                        return $this->render("empty.html.twig", array("status" => "fail"));
                    }
                }
            } else {
                return $this->render("empty.html.twig", array("status" => "fail"));
            }
        } else {
            return $this->render("empty.html.twig", array("status" => "fail"));
        }
    }

    private function assembleConfig($data)
    {
        $config = array();
        foreach ($data as $key => $value) {
            $config[strtolower("config_" . $key)] = $value;
        }
        return $config;
    }

    private function disassembleConfig($data)
    {
        $config = array();
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                if ("config_" === $prefix = substr($key, 0, 7)) {
                    $config[substr($key, 7)] = $value;
                }
            }
        }
        return $config;
    }

    private function getAllPlugins()
    {
        return AbstractScope::registerPlugins();
    }

    private function getInstalledPlugins()
    {
        $result = array();
        $installed = $this->getCache();
        if (count($installed) > 0) {
            foreach ($installed as $pluginName => $plugin) {
                $result[$plugin['scope']][$pluginName] = $plugin;
            }
        }
        return $result;
    }

    private function getUninstalledPlugins()
    {
        $allplugins = $this->getAllPlugins();
        $installed = $this->getInstalledPlugins();
        if (count($installed) > 0) {
            foreach ($installed as $scope => $plugins) {
                if (count($plugins) > 0) {
                    foreach ($plugins as $pluginName => $plugin) {
                        if (isset($allplugins[$scope][$pluginName])) {
                            unset($allplugins[$scope][$pluginName]);
                        }
                    }
                }
            }
        }
        return $allplugins;
    }

    final public function dumpCache()
    {
        // for plugin
        $plugins = array();
        $pluginEntity = $this->getRepo("PluginBundle:Plugin")->getAll();
        if (count($pluginEntity) > 0) {
            foreach ($pluginEntity as $plugin) {
                $plugins[$plugin['name']] = $plugin;
                $plugins[$plugin['name']]['status'] = (int)$plugin['status'];
                $plugins[$plugin['name']]['logo'] = $this->get($plugin['name'] . "Plugin")->getLogo();
            }
        }
        parent::buildCache($plugins, "PluginBundle:Plugin");
    }

}
