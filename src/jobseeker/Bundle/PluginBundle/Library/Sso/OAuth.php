<?php

namespace jobseeker\Bundle\PluginBundle\Library\Sso;

class OAuth
{

    const REVOKE_URI = 'http://sso.jobseeker.com/revoke';
    const TOKEN_URI = 'http://sso.jobseeker.com/token';
    const AUTH_URL = 'http://sso.jobseeker.com/auth';

    protected $client_id;
    protected $client_secret;
    protected $scopes;
    protected $access_token;
    protected $refresh_token;
    protected $postdata;
    protected $http_code;
    protected $http_info;
    protected $url;
    protected $http_header;
    protected $host = "http://sso.jobseeker.com";
    protected $timeout = 30;
    protected $connecttimeout = 30;
    protected $ssl_verifypeer = FALSE;
    protected $format = 'json';
    protected $decode_json = TRUE;
    protected $useragent = 'jobseeker sso oauth client';
    protected $debug = FALSE;
    protected static $boundary = '';

    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function getUser()
    {
        return $this->host . "/user/get";
    }

    public function registerUser()
    {
        return $this->host . "/user/register";
    }

    public function setScopes($scopes = null)
    {
        if (null === $scopes) {
            throw new \Exception("No scopes specified");
        }
        return $this->scopes = $scopes;
    }

    public function getAuthorizeURL($redirect_uri, $response_type = 'code', $state)
    {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['redirect_uri'] = $redirect_uri;
        $params['response_type'] = $response_type;
        $params['state'] = $state;
        $params['scope'] = $this->scopes;
        return self::AUTH_URL . "?" . http_build_query($params, '', '&');
    }

    public function getAccessToken($type = 'code', $keys)
    {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        if ($type === 'token') {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ($type === 'code') {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];
        } elseif ($type === 'password') {
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else {
            throw new \Exception("wrong auth type");
        }
        $response = $this->oAuthRequest(self::TOKEN_URI, 'POST', $params);
        $token = json_decode($response, true);
        if (is_array($token) && !isset($token['error'])) {
            $this->access_token = $token['access_token'];
            $this->refresh_token = $token['refresh_token'];
        } else {
            throw new \Exception("get access token failed." . $token['error']);
        }
        return $token;
    }

    public function revokeToken($token)
    {
        $params = array();
        $params['access_token'] = isset($token['access_token']) ? $token['access_token'] : $this->access_token;
        $response = $this->oAuthRequest($this->revokeTokenURL(), 'POST', $params);
        $token = json_decode($response, true);
        if (is_array($token) && !isset($token['error'])) {
            return (bool) $token['result'];
        } else {
            return false;
        }
    }

    public function getTokenFromArray($arr)
    {
        if (isset($arr['access_token']) && $arr['access_token']) {
            $token = array();
            $this->access_token = $token['access_token'] = $arr['access_token'];
            if (isset($arr['refresh_token']) && $arr['refresh_token']) {
                $this->refresh_token = $token['refresh_token'] = $arr['refresh_token'];
            }

            return $token;
        } else {
            return false;
        }
    }

    public function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    public function post($url, $parameters = array(), $multi = false)
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    public function delete($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    public function oAuthRequest($url, $method, $parameters, $multi = false)
    {

        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }

        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters))) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return $this->http($url, $method, $body, $headers);
        }
    }

    public function http($url, $method, $postfields = NULL, $headers = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        if (version_compare(phpversion(), '5.4.0', '<')) {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
        } else {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        if (isset($this->access_token) && $this->access_token) {
            $headers[] = "Authorization: OAuth2 " . $this->access_token;
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo "=====headers======\r\n";
            print_r($headers);

            echo '=====request info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

    public function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

    public static function build_http_query_multi($params)
    {
        if (!$params) {
            return '';
        }
        uksort($params, 'strcmp');
        $pairs = array();
        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';
        foreach ($params as $parameter => $value) {
            if (in_array($parameter, array('pic', 'image')) && $value{0} == '@') {
                $url = ltrim($value, '@');
                $content = file_get_contents($url);
                $array = explode('?', basename($url));
                $filename = $array[0];
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }
        }
        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }

}
