<?php

namespace jobseeker\Bundle\PluginBundle\Library\Facebook;

class Facebook extends FacebookBase
{
    /**
     * The Cookie Prefix Name.
     *
     * @var const variable
     */
    const COOKIE_PRIFIX = "fb";

    /**
     * The Cookie Expire Time.
     *
     * @var default 1 year
     */
    const COOKIE_EXPIRE = 31556926;

    /**
     * The Request For Weibo User Login, Base On $controller->getRequest()
     *
     */
    protected $request;

    /**
     * The Request Session For Storage, Base On $request->getSession()
     *
     */
    protected $storage;

    /**
     * The Cookie For Weibo User Login, Base On $request->cookies
     *
     */
    protected $cookie;
    protected $sharedSessionID;
    protected static $kSupportedKeys = array('state', 'code', 'access_token', 'user_id');

    public function __construct($config, $request)
    {
        $this->request = $request;
        $this->storage = $request->getSession();
        $this->cookie = $request->cookies;
        parent::__construct($config);
        if (!empty($config['sharedSession'])) {
            $this->initSharedSession();
            $state = $this->getPersistentData('state');
            if (!empty($state)) {
                $this->state = $state;
            } else {
                $this->state = null;
            }
        }
    }

    public function getUserId()
    {
        return $this->getUser();
    }

    public function getUserInfo()
    {
        return $this->api("/me");
    }

    protected function initSharedSession()
    {
        $cookie_name = $this->getSharedSessionCookieName();
        if ($this->cookie->has($cookie_name)) {
            $data = $this->parseSignedRequest($this->cookie->get($cookie_name));
            if ($data && !empty($data['domain']) && self::isAllowedDomain($this->getHttpHost(), $data['domain'])) {
                $this->sharedSessionID = $data['id'];
                return;
            }
        }
        $base_domain = $this->getBaseDomain();
        $this->sharedSessionID = md5(uniqid(mt_rand(), true));
        $cookie_value = $this->makeSignedRequest(
            array(
                'domain' => $base_domain,
                'id' => $this->sharedSessionID,
            )
        );
        $this->cookie->set($cookie_name, $cookie_value);
        if (!headers_sent()) {
            $expire = time() + self::COOKIE_EXPIRE;
            setcookie($cookie_name, $cookie_value, $expire, '/', $base_domain, false, true);
        } else {
            self::errorLog(
                'Shared session ID cookie could not be set! You must ensure you ' .
                'create the Facebook instance before headers have been sent. This ' .
                'will cause authentication issues after the first request.'
            );
        }
    }

    protected function getSharedSessionCookieName()
    {
        return self::COOKIE_PRIFIX . '_' . $this->getAppId();
    }

    protected function getMetadataCookie()
    {
        $cookie_name = $this->getMetadataCookieName();
        if (!$this->cookie->has($cookie_name)) {
            return array();
        }
        $cookie_value = trim($this->cookie->get($cookie_name), '"');
        if (empty($cookie_value)) {
            return array();
        }
        $parts = explode('&', $cookie_value);
        $metadata = array();
        foreach ($parts as $part) {
            $pair = explode('=', $part, 2);
            if (!empty($pair[0])) {
                $metadata[urldecode($pair[0])] = (count($pair) > 1) ? urldecode($pair[1]) : '';
            }
        }
        return $metadata;
    }

    protected function getHttpHost()
    {
        return $this->request->getHost();
    }

    protected function getHttpProtocol()
    {
        return $this->request->getScheme();
    }

    protected function getCurrentUrl()
    {
        $protocol = $this->getHttpProtocol() . '://';
        $host = $this->getHttpHost();
        $currentUrl = $protocol . $host . $this->request->server->get("REQUEST_URI");
        $parts = parse_url($currentUrl);
        $query = '';
        if (!empty($parts['query'])) {
            $params = explode('&', $parts['query']);
            $retained_params = array();
            foreach ($params as $param) {
                if ($this->shouldRetainParam($param)) {
                    $retained_params[] = $param;
                }
            }
            if (!empty($retained_params)) {
                $query = '?' . implode($retained_params, '&');
            }
        }
        $port = isset($parts['port']) && (($protocol === 'http://' && $parts['port'] !== 80) || ($protocol === 'https://' && $parts['port'] !== 443)) ? ':' . $parts['port'] : '';
        return $protocol . $parts['host'] . $port . $parts['path'] . $query;
    }

    public function getSignedRequest()
    {
        if (!$this->signedRequest) {
            if ($signed_request = $this->request->get('signed_request')) {
                $this->signedRequest = $this->parseSignedRequest($signed_request);
            } elseif ($signed_request = $this->cookie->get($this->getSignedRequestCookieName())) {
                $this->signedRequest = $this->parseSignedRequest($signed_request);
            }
        }
        return $this->signedRequest;
    }

//    protected function getCode()
//    {
//        if ($this->request->get('code')) {
//            if ($this->state !== null && $this->request->get('state') && $this->state === $this->request->get('state')) {
//                $this->state = null;
//                $this->clearPersistentData('state');
//                return $this->request->get('code');
//            } else {
//                self::errorLog('CSRF state token does not match one provided.');
//                return false;
//            }
//        }
//        return false;
//    }

    protected function constructSessionVariableName($key)
    {
        $parts = array(self::COOKIE_PRIFIX, $this->getAppId(), $key);
        if ($this->sharedSessionID) {
            array_unshift($parts, $this->sharedSessionID);
        }
        return implode('_', $parts);
    }

    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');
            return;
        }
        $session_var_name = $this->constructSessionVariableName($key);
        $this->storage->set($session_var_name, $value);
    }

    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');
            return $default;
        }
        $session_var_name = $this->constructSessionVariableName($key);
        return $this->storage->has($session_var_name) ? $this->storage->get($session_var_name) : $default;
    }

    protected function clearPersistentData($key)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');
            return;
        }
        $session_var_name = $this->constructSessionVariableName($key);
        if ($this->storage->has($session_var_name)) {
            $this->storage->remove($session_var_name);
        }
    }

    protected function clearAllPersistentData()
    {
        foreach (self::$kSupportedKeys as $key) {
            $this->clearPersistentData($key);
        }
        if ($this->sharedSessionID) {
            $this->deleteSharedSessionCookie();
        }
    }

    protected function deleteSharedSessionCookie()
    {
        $cookie_name = $this->getSharedSessionCookieName();
        $this->cookie->remove($cookie_name);
        $base_domain = $this->getBaseDomain();
        setcookie($cookie_name, '', 1, '/', '.' . $base_domain);
    }

    public function destroySession()
    {
        $this->accessToken = null;
        $this->signedRequest = null;
        $this->user = null;
        $this->clearAllPersistentData();
        // Javascript sets a cookie that will be used in getSignedRequest that we
        // need to clear if we can
        $cookie_name = $this->getSignedRequestCookieName();
        if ($this->cookie->has($cookie_name)) {
            $this->cookie->remove($cookie_name);
            if (!headers_sent()) {
                $base_domain = $this->getBaseDomain();
                setcookie($cookie_name, '', 1, '/', '.' . $base_domain);
            } else {
                self::errorLog(
                    'There exists a cookie that we wanted to clear that we couldn\'t ' .
                    'clear because headers was already sent. Make sure to do the first ' .
                    'API call before outputing anything.'
                );
            }
        }
    }

}
