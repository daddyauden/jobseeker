<?php

namespace jobseeker\Bundle\ToolBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use jobseeker\Bundle\ToolBundle\Service\Maxmind\MaxMind;
use jobseeker\Bundle\ToolBundle\Service\PageService;
use jobseeker\Bundle\ToolBundle\Service\EncryptInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Base extends Controller implements EncryptInterface
{

    const VERSION = '1.0.0';
    const COOKIE_SALT = "*?t}{ak5pMW;/BOPR|voke5bLFN'K3hjQePp(7@)MlLGecqBhN";

    protected $parameters = array();
    protected $entityName = NULL;
    protected $entity = NULL;
    protected $bundleName = NULL;

    protected function autoLogin()
    {
        if ($this->hasCookie('uid')) {
            $data = $this->decodeCookie($this->getCookie("uid"));
            if (is_array($data) && isset($data['logintime'])) {
                $isExpires = ($data['logintime'] + ((int)$this->getSystem("cookie_expires"))) > time() ? true : false;
                if (false === $isExpires) {
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
                    return false;
                }
                return true;
            } else {
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
                return false;
            }
        }

        return false;
    }

    protected function getUid()
    {
        if ($this->hasCookie('uid')) {
            $data = $this->decodeCookie($this->getCookie("uid"));
            return $data;
        }

        return NULL;
    }

    protected function isEmployer($user = NULL)
    {
        if (NULL == $user) {
            return false;
        }

        $prefix = strtoupper($this->getParameter("country"));
        return $this->sismemberRedis($prefix . ":employer", $user['uid']);
    }

    protected function addEmployer($user = NULL)
    {
        if (NULL == $user) {
            return false;
        }

        $prefix = strtoupper($this->getParameter("country"));
        return $this->saddRedis($prefix . ":employer", $user['uid']);
    }

    protected function delEmployer($user = NULL)
    {
        if (NULL == $user) {
            return false;
        }

        $prefix = strtoupper($this->getParameter("country"));
        return $this->sdelRedis($prefix . ":employer", $user['uid']);
    }

    protected function getEntity($entityName = NULL)
    {
        $entityFullName = $entityName === NULL ? $this->getEntityName() : $entityName;

        list($bundleName, $entityBaseName) = explode(":", $entityFullName);

        $entityClass = "jobseeker\\Bundle\\" . $bundleName . "\\Entity\\" . $entityBaseName;

        try {
            $entity = new $entityClass();
            return $entity;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function getEntityName()
    {
        return $this->entityName ?: $this->getBundleName() . ":" . $this->getControllerName();
    }

    protected function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    protected function getActionPrefix()
    {
        return $this->getBundleName() . ':' . $this->getControllerName() . ':';
    }

    protected function getBundleName()
    {
        if (NULL === $this->bundleName) {
            $bundlePath = $this->getBundlePath();
            $this->bundleName = substr($bundlePath, strrpos($bundlePath, '\\') + 1);
        }

        return $this->bundleName;
    }

    protected function getBundlePath()
    {
        return substr($this->reflectionObject()->getNamespaceName(), 0, -11);
    }

    protected function getControllerName()
    {
        $controllerName = $this->reflectionObject()->getShortName();

        return str_replace("Controller", "", $controllerName);
    }

    protected function reflectionObject()
    {
        return new \ReflectionObject($this);
    }

    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function getRepo($entityName = NULL)
    {
        $entityName = $entityName === NULL ? $this->getEntityName() : $entityName;

        if ($entityName === NULL) {
            throw new \RuntimeException(sprintf("Please Set \$entityName Format Like BundleName:EntityName \n"));
        }

        return $this->getEm()->getRepository($entityName);
    }

    protected function getParameter($name = NULL)
    {
        if ($name == NULL) {
            return array_merge($this->container->getParameterBag()->all(), (array)$this->parameters);
        } else if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        } else if ($this->container->hasParameter($name)) {
            return $this->parameters[$name] = $this->container->getParameter($name);
        } else {
            return NULL;
        }
    }

    protected function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    protected function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || $this->container->hasParameter($name);
    }

    protected function buildCache($data, $fileName = NULL)
    {
        $template = <<<TEMPLATE
<?php

/*
 * This file is cache for jobseeker system.
 *
 * filename generate by entity name
 * file that was distributed with this source code.
 */

return %s;

TEMPLATE;

        $data = var_export($data, true);
        $data = preg_replace('/array \(/', 'array(', $data);
        $data = preg_replace('/\n {1,10}array\(/', 'array(', $data);
        $data = preg_replace('/  /', '    ', $data);
        $data = sprintf($template, $data);

        $fileName = $fileName === NULL ? $this->getEntityName() : $fileName;

        list($bundleName, $entityName) = explode(":", $fileName);

        file_put_contents($this->getCacheDir() . DIRECTORY_SEPARATOR . strtolower($bundleName . "_" . $entityName) . '.php', $data);
    }

    protected function dumpCache()
    {
        // for system
        $systems = array();
        $systemEntity = $this->getRepo("AdminBundle:System")->getAll();
        if (count($systemEntity) > 0) {
            foreach ($systemEntity as $system) {
                $systems[$system['skey']] = array(
                    "id" => $system['id'],
                    'skey' => $system['skey'],
                    'stype' => $system['stype'],
                    'svalue' => $system['stype'] == "number" ? (int)$system['svalue'] : $system['svalue']
                );
            }
            $this->setSystem();
        }
        $this->buildCache($systems, "AdminBundle:System");

        // for locale
        $locales = array();
        $localeEntity = $this->getRepo("AdminBundle:Locale")->getAll();
        if (count($localeEntity) > 0) {
            foreach ($localeEntity as $locale) {
                $locales[$locale['id']] = $locale;
                $locales[$locale['id']]['alias'] = $this->trans(strtolower($locale['code'] . ".title"));
            }
        }
        $this->buildCache($locales, "AdminBundle:Locale");

        // for area
        $areas = array();
        $areaEntity = $this->getRepo("AdminBundle:Area")->getAll();
        if (count($areaEntity) > 0) {
            foreach ($areaEntity as $area) {
                $areas[$area['id']] = $area;
                $areas[$area['id']]['alias'] = $this->trans(strtoupper($area['code']));
                if ($area['level'] != 1) {
                    $areas[$area['pid']]['child'][$area['id']] = $areas[$area['id']];
                }
            }
        }
        foreach ($areas as $areaid => $area) {
            if (array_key_exists("child", $area) && !array_key_exists("id", $area)) {
                unset($areas[$areaid]);
            }
        }
        $this->buildCache($areas, "AdminBundle:Area");

        //for admin user
        $admins = array();
        $adminEntity = $this->getRepo("AdminBundle:Admin")->getAll();
        if (count($adminEntity) > 0) {
            foreach ($adminEntity as $admin) {
                $admins[$admin['email']] = array(
                    'id' => $admin['id'],
                    "email" => $admin['email'],
                    'loginip' => $admin['loginip'],
                    'role' => $admin['rid']['name'],
                    'logintime' => date("Y:m:d H:i:s e", $admin['logintime'])
                );
            }
        }
        $this->buildCache($admins, "AdminBundle:Admin");

        // for admin role
        $roles = array();
        $roleEntity = $this->getRepo("UserBundle:Role")->getAll();
        if (count($roleEntity) > 0) {
            foreach ($roleEntity as $role) {
                $roles[$role['id']] = $role;
                $roles[$role['id']]['alias'] = $this->trans('role.' . $role['name']);
            }
        }
        $this->buildCache($roles, "UserBundle:Role");

        // for employer type
        $employertypes = array();
        $employertypeEntity = $this->getRepo("UserBundle:Category")->getAll("employer_type");
        if (count($employertypeEntity) > 0) {
            foreach ($employertypeEntity as $employertype) {
                $employertypes[$employertype['id']] = $employertype;
            }
        }
        $this->buildCache($employertypes, "UserBundle:Employer_Type");

        // for employer scale
        $employerscales = array();
        $employerscaleEntity = $this->getRepo("UserBundle:Category")->getAll("employer_scale");
        if (count($employerscaleEntity) > 0) {
            foreach ($employerscaleEntity as $employerscale) {
                $employerscales[$employerscale['id']] = $employerscale;
            }
        }
        $this->buildCache($employerscales, "UserBundle:Employer_Scale");

        // for product
        $products = array();
        $productEntity = $this->getRepo("CareerBundle:Product")->getAll();
        if (count($productEntity) > 0) {
            foreach ($productEntity as $product) {
                $products[$product['id']] = $product;
            }
        }
        $this->buildCache($products, "CareerBundle:Product");

        // for type
        $types = array();
        $typeEntity = $this->getRepo("CareerBundle:Type")->getAll();
        if (count($typeEntity) > 0) {
            foreach ($typeEntity as $type) {
                $types[$type['id']] = $type;
            }
        }
        $this->buildCache($types, "CareerBundle:Type");

        // for industry
        $industries = array();
        $industryEntity = $this->getRepo("CareerBundle:Industry")->getAll();
        if (count($industryEntity) > 0) {
            foreach ($industryEntity as $industry) {
                $industries[$industry['id']] = $industry;
                if ($industry['pid'] != 0) {
                    $industries[$industry['pid']]['child'][$industry['id']] = $industry;
                }
            }
        }
        foreach ($industries as $industryid => $industry) {
            if (array_key_exists("child", $industry) && !array_key_exists("id", $industry)) {
                unset($industries[$industryid]);
            }
        }
        $this->buildCache($industries, "CareerBundle:Industry");
    }

    protected function getCache($fileName = NULL)
    {
        $fileName = $fileName === NULL || empty($fileName) ? $this->getEntityName() : $fileName;

        list($bundleName, $entityName) = explode(":", $fileName);

        $file = $this->getCacheDir() . DIRECTORY_SEPARATOR . strtolower($bundleName . "_" . $entityName) . '.php';

        if (false === file_exists($file)) {
            $this->dumpCache();
        }

        if (false === file_exists($file)) {
            return array();
        }

        return include $file;
    }

    private function getCacheDir()
    {
        $dir = $this->getParameter("kernel.cache_dir") . DIRECTORY_SEPARATOR . "jobseeker";
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory \n", $dir));
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory \n", $dir));
            }
        }

        return $dir;
    }

    protected function getCookieLocale()
    {
        return $this->getCookie("locale");
    }

    protected function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    protected function getClientLanguage()
    {
        return $this->getRequest()->getPreferredLanguage();
    }

    protected function getClientLanguages()
    {
        return $this->getRequest()->getLanguages();
    }

    protected function getCountry($ip = NULL, $by = "all")
    {
        $ip = $ip === NULL ? $this->getClientIp() : $ip;

        return MaxMind::getCountry($ip, $by);
    }

    protected function getCity($ip = NULL, $by = "all")
    {
        $ip = $ip === NULL ? $this->getClientIp() : $ip;

        return MaxMind::getCity($ip, $by);
    }

    protected function getClientIp()
    {
        return $this->getRequest()->getClientIp();
    }

    protected function createCookie(array $cookie)
    {
        return new Cookie(strtoupper($cookie['name']), $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
    }

    protected function sendCookie(array $cookie)
    {
        $cookie = $this->createCookie($cookie);
        $response = Response::create();
        $response->headers->setCookie($cookie);
        $response->sendHeaders();
    }

    protected function setLocale($locale)
    {
        $this->getRequest()->setLocale($locale);
    }

    protected function isAjax()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    protected function isGet()
    {
        return $this->getRequest()->getMethod() === "GET";
    }

    protected function isPost()
    {
        return $this->getRequest()->getMethod() === "POST";
    }

    protected function isPatch()
    {
        return $this->getRequest()->getMethod() === "PATCH";
    }

    protected function isPut()
    {
        return $this->getRequest()->getMethod() === "PUT";
    }

    protected function isDelete()
    {
        return $this->getRequest()->getMethod() === "DELETE";
    }

    protected function trans($mess, array $parameters = array())
    {
        return $this->get("translator")->trans($mess, $parameters);
    }

    protected function getFormData($name = NULL)
    {
        $form = $this->getPost("form");
        if ($name === NULL) {
            return $form;
        }

        return isset($form[$name]) ? $form[$name] : NULL;
    }

    protected function getQuery($name = NULL)
    {
        return $this->getRequestData("query", $name);
    }

    protected function getPost($name = NULL)
    {
        return $this->getRequestData("request", $name);
    }

    protected function getCookie($name = NULL)
    {
        return $this->getRequestData("cookies", strtoupper($name));
    }

    protected function getServer($name = NULL)
    {
        return $this->getRequestData("server", $name);
    }

    protected function getHeader($name = NULL)
    {
        return $this->getRequestData("headers", $name);
    }

    protected function getFile($name = NULL)
    {
        return $this->getRequestData("files", $name);
    }

    protected function hasSession($name)
    {
        return $this->get("session")->has($name);
    }

    protected function getSession($name = NULL, $default = NULL)
    {
        if (NULL === $name) {
            return $this->get("session")->all();
        }

        return $this->get("session")->get($name, $default);
    }

    protected function setSession($name, $value)
    {
        return $this->get("session")->set($name, $value);
    }

    protected function replaceSession(array $attributes = array())
    {
        return $this->get("session")->replace($attributes);
    }

    protected function removeSession($name)
    {
        return $this->get("session")->remove($name);
    }

    protected function clearSession()
    {
        return $this->get("session")->clear();
    }

    protected function getSessionId()
    {
        return $this->getRequest()->getSession()->getId();
    }

    protected function getSessionName()
    {
        return $this->getRequest()->getSession()->getName();
    }

    protected function hasPreviousSession()
    {
        return $this->getRequest()->hasPreviousSession();
    }

    private function getRequestData($key, $name)
    {
        if ($name === NULL) {
            return $this->getRequest()->$key->all();
        }

        return $this->getRequest()->$key->get($name);
    }

    protected function getSchemeAndHttpHost()
    {
        return $this->getRequest()->getSchemeAndHttpHost();
    }

    protected function hasQuery($name = NULL)
    {
        return $this->hasRequestData("query", $name);
    }

    protected function hasPost($name = NULL)
    {
        return $this->hasRequestData("request", $name);
    }

    protected function hasCookie($name = NULL)
    {
        return $this->hasRequestData("cookies", strtoupper($name));
    }

    protected function hasServer($name = NULL)
    {
        return $this->hasRequestData("server", $name);
    }

    protected function hasHeader($name = NULL)
    {
        return $this->hasRequestData("headers", $name);
    }

    private function hasRequestData($key, $name)
    {
        if ($name === NULL) {
            return false;
        }

        return $this->getRequest()->$key->has($name);
    }

    final public function getVersion()
    {
        return self::VERSION;
    }

    protected function getBundle($name = NULL)
    {
        $bundles = $this->getBundles();
        if ($name === NULL || !isset($bundles[$name])) {
            return $bundles;
        } else {
            return $bundles[$name];
        }
    }

    protected function getBundles()
    {
        $bundles = array();
        foreach ($this->get("kernel")->getBundles() as $name => $bundle) {
            if (strpos(strtolower($bundle->getNamespace()), "jobseeker\bundle") === 0) {
                $bundles[$name] = $bundle;
            } else {
                continue;
            }
        }

        return $bundles;
    }

    protected function getControllerInBundle(BundleInterface $bundle)
    {
        $path = $bundle->getPath() . DIRECTORY_SEPARATOR . "Controller" . DIRECTORY_SEPARATOR . "*Controller.php";
        $files = glob($path);
        if (is_array($files) && count($files) == 0) {
            return NULL;
        } else {
            $controllers = array();
            foreach ($files as $file) {
                $controllerName = str_replace("Controller.php", "", basename($file));
                $controllerFullName = $bundle->getNamespace() . "\\Controller\\" . $controllerName . "Controller";
                $controllers[$controllerName] = $controllerFullName;
            }
            return $controllers;
        }
    }

    protected function getActionInController($controllerFullName)
    {
        $controller = new \ReflectionClass($controllerFullName);
        $methods = array();
        foreach ($controller->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isUserDefined() && strpos($method->getName(), "Action") !== false) {
                $methods[] = str_replace("action", "", strtolower($method->getName()));
            }
        }

        return $methods;
    }

    protected function getRouteBaseAction($bundleName, $controllerName, $actionName, $onlyRouteName = true)
    {
        $_controller = sprintf("jobseeker\Bundle\%s\Controller\%sController::%sAction", ucwords($bundleName), ucwords($controllerName), strtolower($actionName));
        foreach ($this->get("router")->getRouteCollection()->all() as $routeName => $route) {
            if ($_controller == $route->getDefault("_controller")) {
                if ($onlyRouteName === true) {
                    return $routeName;
                }
                return $route;
            }
        }

        return "";
    }

    protected function generatePager($pattern, $current = 1, $offset = 10, $total = 10)
    {
        return new PageService($pattern, $current, $offset, $total, $this->get("translator"));
    }

    protected function getPerPage()
    {
        return $this->getSystem("per_page") ?: 10;
    }

    protected function getSystem($field)
    {
        $prefix = strtoupper($this->getParameter("country"));
        if ($value = $this->hgetRedis($prefix . ":system", $field)) {
            return $value;
        }

        return $this->getParameter($field);
    }

    protected function setSystem()
    {
        $prefix = strtoupper($this->getParameter("country"));
        $system = array();

        $systemDB = $this->getRepo("AdminBundle:System")->getAll();

        if (count($systemDB) > 0) {
            foreach ($systemDB as $value) {
                $system[$value['skey']] = $value['stype'] == "number" ? (int)$value['svalue'] : $value['svalue'];
            }
            $this->hmsetRedis($prefix . ":system", $system);
        }

        if ($value = $this->getParameter("timezone")) {
            $timezone = $value;
        } else if ($value = $this->hgetRedis($prefix . ":system", "timezone")) {
            $timezone = $value;
        } else {
            $timezone = ini_get('date.timezone');
        }

        if ($timezone) {
            date_default_timezone_set($timezone);
            ini_set('date.timezone', $timezone);
        }
    }

    protected function hasSystem($field)
    {
        $prefix = strtoupper($this->getParameter("country"));
        return $this->hexistsRedis($prefix . ":system", $field) ? true : $this->hasParameter($field) ? true : false;
    }

    protected function getArea()
    {

        $data = array();
        $areas = $this->getCache("AdminBundle:Area");

        if (count($areas) == 0) {
            $areas = $this->getRepo("AdminBundle:Area")->getAvailableCountry();
        }

        if (count($areas) > 0) {
            foreach ($areas as $aid => $area) {
                if ($area['level'] == 1 && $area['status'] == 1) {
                    $data[$area['id']]['code'] = strtoupper($area['code']);
                    if (array_key_exists("child", $area)) {
                        foreach ($area['child'] as $childaid => $child) {
                            if ($child['status'] == 1) {
                                $data[$child['pid']]['child'][$childaid]['code'] = strtoupper($child['code']);
                                $data[$child['pid']]['child'][$childaid]['domain'] = $child['domain'];
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    protected function getLocale()
    {
        $data = array();
        $defaultLocale = $this->getRequest()->getLocale();
        array_push($data, strtolower($defaultLocale));

        $locales = $this->getCache("AdminBundle:Locale");

        if (count($locales) == 0) {
            $locales = $this->getRepo("AdminBundle:Locale")->getAvailable();
        }

        if (count($locales) > 0) {
            foreach ($locales as $locale) {
                if ((int)$locale['status'] === 1) {
                    array_push($data, strtolower($locale['code']));
                }
            }
        }

        $locales = array_unique($data);

        if (count($locales) > 10) {
            return array("single" => false, "data" => array_chunk($locales, 10));
        }

        return array("single" => true, "data" => $locales);
    }

    protected function printCookieExpire($timestamp = NULL)
    {
        if ($timestamp === NULL) {
            $timestamp = time();
        }

        $timestamp = $timestamp + $this->getSystem("cookie_expires");

        $datetime = new \DateTime();

        $datetime->setTimestamp($timestamp);

        return $datetime->format(\DateTime::COOKIE);
    }

    protected function encodeCookie($data)
    {
        return $this->encode($data, self::COOKIE_SALT);
    }

    protected function decodeCookie($data)
    {
        return $this->decode($data, self::COOKIE_SALT);
    }

    public function safeB64Encode($data)
    {
        $b64 = base64_encode($data);

        return str_replace(array('+', '/', "\r", "\n", '='), array('-', '_'), $b64);
    }

    public function safeB64Decode($b64)
    {
        $b64 = str_replace(array('-', '_'), array('+', '/'), $b64);

        return base64_decode($b64);
    }

    public function encode($data, $salt = NULL)
    {
        $salt = $salt === NULL ? self::SALT : $salt;
        $b64 = $this->safeB64Encode(serialize($data)) . $salt;
        $len1 = strlen($b64);
        $len2 = strlen($salt);
        $digital = "";

        for ($i = 0; $i < $len1; $i++) {
            $digital .= chr(ord($b64[$i]) ^ $len2);
        }

        return $this->safeB64Encode($digital);
    }

    public function decode($data, $salt = NULL)
    {
        $salt = $salt === NULL ? self::SALT : $salt;
        $b64 = $this->safeB64Decode($data);
        $len1 = strlen($b64);
        $len2 = strlen($salt);
        $digital = "";

        for ($i = 0; $i < $len1; $i++) {
            $digital .= chr(ord($b64[$i]) ^ $len2);
        }

        return unserialize($this->safeB64Decode(substr($digital, 0, -$len2)));
    }

    final protected function encrypt($data)
    {
        $method = self::ENCRYPT;

        return $method($data);
    }

    protected function brandIntro($content = NULL, $getFirst = false)
    {
        if ($content === NULL) {
            $content = $this->trans("common.brand.intro");
        }
        $first = "";
        $firstFinded = false;
        $systemLocale = $this->getRequest()->getLocale();
        $brandArr = array();
        $brandIntro = explode("#", $content);
        foreach ($brandIntro as $data) {
            $brand = explode("@", $data);
            $locale = strtolower($brand[0]);
            if ($systemLocale === $locale) {
                $first = $brand[1];
                $firstFinded = true;
            } else if (strpos($systemLocale, $locale) === 0 && $firstFinded === false) {
                $first = $brand[1];
            } else {
                array_push($brandArr, $brand[1]);
            }
        }

        if ($getFirst === true) {
            return $first;
        } else {
            array_unshift($brandArr, $first);
            return json_encode($brandArr);
        }
    }

    protected function sendEmailByTemplate($subject, $to, $template, array $parameter = array(), $contentType = "text/html", $from = NULL, $attach = NULL, $cc = NULL, $bcc = NULL)
    {
        $message = \Swift_Message::newInstance()->setSubject($subject)->setTo($to);
        $message->setBody($this->renderView($template, $parameter), $contentType);

        if (NULL !== $cc) {
            $message->setCc($cc);
        }

        if (NULL !== $bcc) {
            $message->setBcc($bcc);
        }

        if (NULL === $from) {
            $from = $this->getParameter("mailer_sender_address");
        }

        if (NULL != $attach) {
            $message->attach(\Swift_Attachment::fromPath($attach));
        }

        $message->setPriority(1);

        $message->setSender($from);

        $message->setReplyTo($from);

        $message->setFrom($from, "JobSeeker " . $this->trans(strtoupper($this->getParameter("country"))));

        $this->get("mailer")->send($message, $failedRecipients);

        return $failedRecipients;
    }

    protected function sendEmailByBody($subject, $to, $body, $contentType = "text/html", $from = NULL, $cc = NULL, $bcc = NULL)
    {
        $message = \Swift_Message::newInstance()->setSubject($subject)->setTo($to);
        $message->setBody($body, $contentType);

        if (NULL !== $cc) {
            $message->setCc($cc);
        }

        if (NULL !== $bcc) {
            $message->setBcc($bcc);
        }

        if (NULL === $from) {
            $from = $this->getSystem("mailer_sender_address");
        }

        $message->setPriority(1);

        $message->setSender($from);

        $message->setReplyTo($from);

        $message->setFrom($from, "JobSeeker " . $this->trans(strtoupper($this->getParameter("country"))));

        $this->get("mailer")->send($message, $failedRecipients);

        return $failedRecipients;
    }

    protected function sendUserValidateEmail($to, $validateURL, $cc = NULL)
    {
        $parameter = array();
        $parameter["host"] = $this->getSystem("host");
        $parameter["body"] = $this->trans("user.validate.body");
        $parameter["validateurl"] = $validateURL;
        $parameter["subject"] = $this->trans("user.validate.subject");
        $parameter["notice_a"] = $this->trans("user.validate.notice_a", array("%validateurl%" => $validateURL));
        $parameter["notice_b"] = $this->trans("user.validate.notice_b", array("%support_email%" => $this->getSystem("support_email")));

        return $this->sendEmailByTemplate($parameter["subject"], $to, "tool/user/signup.html.twig", $parameter, "text/html", NULL, NULL, $cc, NULL);
    }

    protected function sendCV($to, $joburl, $mailfrom, $cv = NULL)
    {
        $parameter = array();
        $parameter["host"] = $this->getSystem("host");
        $parameter["body"] = $this->trans("career.sendcv.body");
        $parameter["joburl"] = $joburl;
        $parameter["subject"] = $this->trans("career.sendcv.subject");
        $parameter["notice_a"] = $this->trans("career.sendcv.notice_a", array("%joburl%" => $joburl));
        $parameter["notice_b"] = $this->trans("career.sendcv.notice_b", array("%support_email%" => $this->getSystem("support_email")));

        return $this->sendEmailByTemplate($parameter["subject"], $to, "tool/career/sendcv.html.twig", $parameter, "text/html", $mailfrom, $cv);
    }

    protected function sendDeliveryEmail($to, $subject, $body)
    {
        return $this->sendEmailByBody($subject, $to, $body);
    }

    protected function getProductForSearch()
    {
        $data = array();
        $productArr = array();
        $prefix = strtoupper($this->getParameter("country"));

        if ($this->existsRedis($prefix . ":product")) {
            $productIds = $this->smembersRedis($prefix . ":product");
            foreach ($productIds as $productid) {
                if ($this->existsRedis($prefix . ":product:" . $productid)) {
                    $data[$productid] = $this->hgetallRedis($prefix . ":product:" . $productid);
                }
            }
        } else {
            $products = $this->getRepo("CareerBundle:Product")->getAvailable();
            if (count($products) > 0) {
                foreach ($products as $product) {
                    $data[$product['id']] = $product;
                    $this->saddRedis($prefix . ":product", $product['id']);
                    foreach ($product as $key => $value) {
                        $this->hsetRedis($prefix . ":product:" . $product['id'], $key, $value);
                    }
                }

                if (count($data) < 0) {
                    return array();
                }
            } else {
                return array();
            }
        }

        if (count($data) > 0) {
            foreach ($data as $productId => $product) {
                $productName = ($product['duration'] / (3600 * 24)) . " " . $this->trans("common.job.post") . " / " . $this->trans($this->getSystem("currency") . ".symbol") . ($product['price'] == 0 ? $this->trans("table.product.price.0") : $product['price']);
                $productArr[$productId] = $productName;
            }
        }

        return $productArr;
    }

    protected function saveProductToRedis($entity, $op = NULL)
    {

        $prefix = strtoupper($this->getParameter("country"));
        $id = intval($entity->getId());
        $duration = intval($entity->getDuration());
        $price = doubleval($entity->getPrice());
        $queue = intval($entity->getQueue());
        $status = intval($entity->getStatus());
        if ($op == NULL) {
            $op = $status == 1 ? 'add' : "del";
        }

        if ($op == "add") {
            $this->saddRedis($prefix . ":product", $id);
            $this->hmsetRedis($prefix . ":product:" . $id, array(
                "id" => $id,
                "status" => $status,
                "queue" => $queue,
                "price" => $price,
                "duration" => $duration
            ));
        }

        if ($op == "del" && $this->sismemberRedis($prefix . ":product", $id)) {
            $this->sdelRedis($prefix . ":product", $id);
            $this->delRedis($prefix . ":product:" . $id);
        }
    }

    protected function getTypeForSearch()
    {
        $data = array();
        $typeArr = array();
        $prefix = strtoupper($this->getParameter("country"));

        if ($this->existsRedis($prefix . ":type")) {
            $data = $this->hgetallRedis($prefix . ":type");
        } else {
            $types = $this->getRepo("CareerBundle:Type")->getAvailable();
            if (count($types) > 0) {
                foreach ($types as $type) {
                    $data[$type['id']] = strtoupper($type['tsn']);
                }

                $this->hmsetRedis($prefix . ":type", $data);
                if (count($data) < 0) {
                    return array();
                }
            } else {
                return array();
            }
        }

        foreach ($data as $typeId => $tsn) {
            $typeArr[$this->trans($tsn)] = $typeId;
        }

        return $typeArr;
    }

    protected function saveTypeToRedis($entity, $op = NULL)
    {
        $prefix = strtoupper($this->getParameter("country"));
        $id = (int)$entity->getId();
        $code = strtoupper($entity->getTsn());
        $status = (int)$entity->getStatus();
        if ($op == NULL) {
            $op = $status == 1 ? 'add' : "del";
        }

        if ($op == "add") {
            $this->hsetRedis($prefix . ":type", $id, $code);
        }

        if ($op == "del" && $this->hexistsRedis($prefix . ":type", $id)) {
            $this->hdelRedis($prefix . ":type", $id);
        }
    }

    protected function getIndustryForSearch()
    {
        $data = array();
        $industryArr = array();
        $prefix = strtoupper($this->getParameter("country"));

        if ($this->existsRedis($prefix . ":industry")) {
            $industries = $this->hgetallRedis($prefix . ":industry");
            foreach ($industries as $id => $name) {
                $data[$id]["name"] = $name;
                if ($this->existsRedis($prefix . ":industry:" . $id)) {
                    $subIndustries = $this->hgetallRedis($prefix . ":industry:" . $id);
                    foreach ($subIndustries as $subid => $subname) {
                        $data[$id]["sub"][$subid] = $subname;
                    }
                }
            }
        } else {
            $industries = $this->getRepo("CareerBundle:Industry")->getAvailable();
            if (count($industries) > 0) {
                foreach ($industries as $industry) {
                    $id = (int)$industry['id'];
                    $pid = (int)$industry['pid'];
                    $isn = strtoupper($industry['isn']);
                    if (!$pid) {
                        $data[$id]['name'] = $isn;
                        $this->hsetRedis($prefix . ":industry", $id, $isn);
                    } else {
                        $data[$pid]['sub'][$id] = $isn;
                        $this->hsetRedis($prefix . ":industry:" . $pid, $id, $isn);
                    }
                }
            } else {
                return array();
            }
        }

        foreach ($data as $id => $industry) {
            if (key_exists("sub", $industry)) {
                foreach ($industry['sub'] as $childId => $childName) {
                    $industryArr[$id]['name'] = $this->trans($industry['name']);
                    $industryArr[$id]["sub"][$childId] = $this->trans($childName);
                }
            }
        }

        return $industryArr;
    }

    protected function saveIndustryToRedis($entity, $op = NULL)
    {
        $prefix = strtoupper($this->getParameter("country"));
        $pid = (int)$entity->getPid();
        $id = (int)$entity->getId();
        $code = strtoupper($entity->getIsn());
        $status = (int)$entity->getStatus();
        if ($op == NULL) {
            $op = $status == 1 ? 'add' : "del";
        }

        if (!$pid) {
            if ($op == "add") {
                $this->hsetRedis($prefix . ":industry", $id, $code);
                $industries = $this->getRepo("CareerBundle:Industry")->getAvailableSubIndustry($id);
                if (count($industries) > 0) {
                    foreach ($industries as $industry) {
                        $this->hsetRedis($prefix . ":industry:" . $id, $industry['id'], strtoupper($industry['isn']));
                    }
                }
            }

            if ($op == "del" && $this->hexistsRedis($prefix . ":industry", $id)) {
                $this->hdelRedis($prefix . ":industry", $id);
            }
        } else {
            if ($op == "add") {
                $this->hsetRedis($prefix . ":industry:" . $pid, $id, $code);
            }

            if ($op == "del" && $this->hexistsRedis($prefix . ":industry:" . $pid, $id)) {
                $this->hdelRedis($prefix . ":industry:" . $pid, $id);
            }
        }
    }

    protected function getAreaForSearch()
    {
        $data = array();
        $areaArr = array();
        $prefix = strtoupper($this->getParameter("country"));

        if ($this->existsRedis($prefix . ":area")) {
            $areas = $this->hgetallRedis($prefix . ":area");
            foreach ($areas as $id => $code) {
                $data[$id]["name"] = strtoupper($code);
                if ($this->existsRedis($prefix . ":area:" . $id)) {
                    $subAreas = $this->hgetallRedis($prefix . ":area:" . $id);
                    foreach ($subAreas as $subid => $subCode) {
                        $data[$id]["sub"][$subid] = strtoupper($subCode);
                    }
                }
            }
        } else {
            $cities = $this->getRepo("AdminBundle:Area")->getAvailableCity($this->getParameter("country"));
            if (count($cities) > 0) {
                foreach ($cities as $area) {
                    $level = (int)$area['level'];
                    $id = (int)$area['id'];
                    $pid = (int)$area['pid'];
                    $code = strtoupper($area['code']);
                    if ($level === 3) {
                        $data[$id]['name'] = $code;
                        $this->hsetRedis($prefix . ":area", $id, $code);
                    } else if ($level === 4) {
                        $data[$pid]['sub'][$id] = $code;
                        $this->hsetRedis($prefix . ":area:" . $pid, $id, $code);
                    } else {
                        continue;
                    }
                }

                if (count($data) < 0) {
                    return array();
                }
            } else {
                return array();
            }
        }

        foreach ($data as $id => $area) {
            if (key_exists("sub", $area)) {
                foreach ($area['sub'] as $childId => $childName) {
                    $areaArr[$id]['name'] = $this->trans($area['name']);
                    $areaArr[$id]['sub'][$childId] = $this->trans($childName);
                }
            }
        }

        return $areaArr;
    }

    protected function saveAreaToRedis($entity, $op = NULL)
    {
        $prefix = strtoupper($this->getParameter("country"));
        $pid = (int)$entity->getPid();
        $level = (int)$entity->getLevel();
        $id = (int)$entity->getId();
        $code = strtoupper($entity->getCode());
        $status = (int)$entity->getStatus();
        if ($op == NULL) {
            $op = $status == 1 ? 'add' : "del";
        }

        if ($level === 4) {
            if ($op == "add") {
                $this->hsetRedis($prefix . ":area:" . $pid, $id, $code);
            }

            if ($op == "del" && $this->hexistsRedis($prefix . ":area:" . $pid, $id)) {
                $this->hdelRedis($prefix . ":area:" . $pid, $id);
            }
        }

        if ($level === 3) {
            if ($op == "add") {
                $this->hsetRedis($prefix . ":area", $id, $code);
                $districts = $this->getRepo("AdminBundle:Area")->getAvailableDistrict($id);
                if (count($districts) > 0) {
                    foreach ($districts as $district) {
                        $this->hsetRedis($prefix . ":area:" . $id, $district['id'], strtoupper($district['code']));
                    }
                }
            }

            if ($op == "del" && $this->hexistsRedis($prefix . ":area", $id)) {
                $this->hdelRedis($prefix . ":area", $id);
            }
        }
    }

    protected function getUploadRootPath()
    {
        $path = $this->getParameter("upload_dir");
        $path = $path ?: $this->getParameter("kernel.root_dir") . "/../web/media";

        return $path;
    }

    public function getAvatorRootPath()
    {
        $path = $this->getParameter("upload_avator_dir");
        $path = $path ?: $this->getUploadRootPath() . "/avator";

        return $path;
    }

    public function getAttachmentRootPath()
    {
        $path = $this->getParameter("upload_attachment_dir");
        $path = $path ?: $this->getUploadRootPath() . "/attachment";

        return $path;
    }

    protected function getUploadRootUrl()
    {
        $url = $this->getParameter("upload_url");
        $url = $url ?: "/media";

        return $url;
    }

    public function getAvatorRootUrl()
    {
        $url = $this->getParameter("upload_avator_url");
        $url = $url ?: $this->getUploadRootUrl() . "/avator";

        return $url;
    }

    public function getAttachmentRootUrl()
    {
        $url = $this->getParameter("upload_attachment_url");
        $url = $url ?: $this->getUploadRootUrl() . "/attachment";

        return $url;
    }

    public function encodeUploadFileName($name)
    {
        $year = date("Y");
        $month = date("n");
        $day = date("j");
        $fileName = sha1($name . uniqid(mt_rand(), true) . microtime());

        return array(
            "path" => "$year/$month/$day/",
            "name" => "$fileName"
        );
    }

    public function decodeUploadFileName($filename)
    {
        return array(
            "path" => dirname($filename) . "/",
            "name" => basename($filename)
        );
    }

    public function deleteAvator($avator)
    {
        $file = $this->getAvatorRootPath() . '/' . $avator;
        if (is_dir($file)) {
            return;
        } else {
            try {
                if (is_file($file) && file_exists($file)) {
                    unlink($file);

                    $file_1x = $this->getAvatorRootPath() . '/' . dirname($avator) . "/1x_" . basename($avator);

                    if (is_file($file_1x) && file_exists($file_1x)) {
                        unlink($file_1x);
                    }

                    $file_2x = $this->getAvatorRootPath() . '/' . dirname($avator) . "/2x_" . basename($avator);

                    if (is_file($file_2x) && file_exists($file_2x)) {
                        unlink($file_2x);
                    }
                }
            } catch (\Exception $e) {

            }
        }
    }

    public function getUploadMaxSize()
    {
        return $this->getSystem("upload_max_size") ?: UploadedFile::getMaxFilesize();
    }

    private function formatForJS($s_date, $s_time)
    {
        $js_time_format = NULL;

        $js_date_format = NULL;

        if ($s_date) {
            if (strpos($s_date, '/')) {
                list($m, $d, $y) = explode("/", $s_date);
                $js_date_format = "$m$m/$d$d/$y$y$y$y";
            } else if (strpos($s_date, '-')) {
                list($d, $m, $y) = explode("-", $s_date);
                $js_date_format = "$d$d-$m$m-$y$y$y$y";
            }
        }

        if ($s_time) {
            list($h, $m, $s) = explode(":", $s_time);
            $js_time_format = "$h$h:$m$m:$s$s";
        }

        if ($js_date_format && $js_time_format) {
            return strtolower($js_date_format . " " . $js_time_format);
        } else if ($js_date_format) {
            return strtolower($js_date_format);
        } else {
            return strtolower($js_time_format);
        }

        return NULL;
    }

    public function convertDateTimeFormatForJS($s_datetime = NULL)
    {
        if (NULL == $s_datetime) {
            $s_datetime = $this->getSystem("datetime_format");
        }

        list($date, $time) = explode(" ", $s_datetime);

        return $this->formatForJS($date, $time);

    }

    public function convertDateFormatForJS($s_date = NULL)
    {
        if (NULL == $s_date) {
            $s_date = $this->getSystem("date_format");
        }

        if (NULL == $s_date) {
            return NULL;
        }

        return $this->formatForJS($s_date, NULL);
    }

    public function convertTimeFormatForJS($s_time = NULL)
    {
        if (NULL == $s_time) {
            $s_time = $this->getSystem("time_format");
        }

        if (NULL == $s_time) {
            return NULL;
        }

        return $this->formatForJS(NULL, $s_time);

    }

    protected function delRedis($key)
    {
        return $this->get("snc_redis.default")->del($key);
    }

    protected function setRedis($key, $value)
    {
        return $this->get("snc_redis.default")->set($key, $value);
    }

    protected function getRedis($key)
    {
        return $this->get("snc_redis.default")->get($key);
    }

    protected function hexistsRedis($key, $field)
    {
        return $this->get("snc_redis.default")->hexists($key, $field);
    }

    protected function hdelRedis($key, $field)
    {
        return $this->get("snc_redis.default")->hdel($key, $field);
    }

    protected function hsetRedis($key, $field, $value)
    {
        return $this->get("snc_redis.default")->hset($key, $field, $value);
    }

    protected function hmsetRedis($key, $key_value_array)
    {
        return $this->get("snc_redis.default")->hmset($key, $key_value_array);
    }

    protected function hgetRedis($key, $field)
    {
        return $this->get("snc_redis.default")->hget($key, $field);
    }

    protected function hmgetRedis($key, $key_array)
    {
        return $this->get("snc_redis.default")->hmget($key, $key_array);
    }

    protected function hgetallRedis($key)
    {
        return $this->get("snc_redis.default")->hgetall($key);
    }

    protected function existsRedis($key)
    {
        return $this->get("snc_redis.default")->exists($key);
    }

    protected function saddRedis($key, $value)
    {
        return $this->get("snc_redis.default")->sadd($key, $value);
    }

    protected function sdelRedis($key, $value)
    {
        return $this->get("snc_redis.default")->srem($key, $value);
    }

    protected function sismemberRedis($key, $value)
    {
        return $this->get("snc_redis.default")->sismember($key, $value);
    }

    protected function smembersRedis($key)
    {
        return $this->get("snc_redis.default")->smembers($key);
    }

}
