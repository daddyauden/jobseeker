<?php

namespace jobseeker\Bundle\PluginBundle\Library\Weixin;

class WeixinOpenOAuthV2
{

    public $appid;
    public $appsecret;
    public $redirect_uri;
    public $scope;
    public $access_token;
    public $openid;
    public $unionid;
    public $host = "https://api.weixin.qq.com/sns/oauth2/";
    public $format = 'json';
    public static $boundary = '';
    public $http_info;
    public $useragent = 'Weixin Open OAuth2';
    public $connecttimeout = 30;
    public $timeout = 30;
    public $ssl_verifypeer = FALSE;
    public $postdata;
    public $refresh_token;
    public $http_code;
    public $url;
    public $decode_json = TRUE;
    public $debug = false;

    function authorizeURL()
    {
        return 'https://open.weixin.qq.com/connect/qrconnect';
    }

    function accessTokenURL()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    function refreshTokenURL()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    }

    function userInfoURL()
    {
        return 'https://api.weixin.qq.com/sns/userinfo';
    }

    function __construct($appid, $appsecret, $redirect_uri, $access_token = null, $other = null)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        $this->redirect_uri = $redirect_uri;
        $this->access_token = $access_token;
        if ($other !== null) {
            foreach ($other as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    function getAuthorizeURL($response_type = 'code', $state = NULL)
    {
        $params = array();
        $params['appid'] = $this->appid;
        $params['redirect_uri'] = $this->redirect_uri;
        $params['response_type'] = $response_type;
        $params['state'] = $state;
        $params['scope'] = $this->scope;
        return $this->authorizeURL() . "?" . http_build_query($params, '', '&');
    }

    function setScope($scope)
    {
        if (empty($scope)) {
            throw new \Exception("No scope specified");
        }

        return $this->scope = $scope;
    }

    function getAccessToken($type = 'code', $keys)
    {
        $params = array();
        $params['appid'] = $this->appid;
        $params['secret'] = $this->appsecret;
        if ($type === 'token') {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ($type === 'code') {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
        } elseif ($type === 'password') {
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else {
            throw new \Exception("wrong auth type");
        }
        $response = $this->oAuthRequest($this->accessTokenURL(), 'GET', $params);
        $token = json_decode($response, true);
        if (is_array($token) && !isset($token['errcode'])) {
            $this->access_token = $token['access_token'];
            $this->refresh_token = $token['refresh_token'];
            $this->openid = $token['openid'];
            $this->scope = $token['scope'];
            $this->unionid = $token['unionid'];
        } else {
            throw new \Exception("get access token failed." . $token['errcode'], $token['errmsg']);
        }
        return $token;
    }

    function refreshToken($token)
    {
        $params = array();
        $params['appid'] = $this->appid;
        $params['grant_type'] = 'refresh_token';
        $params['refresh_token'] = isset($token['refresh_token']) ? $token['refresh_token'] : $this->refresh_token;
        $response = $this->oAuthRequest($this->refreshTokenURL(), 'GET', $params);
        $token = json_decode($response, true);
        if (is_array($token) && !isset($token['errcode'])) {
            $this->access_token = $token['access_token'];
            $this->refresh_token = $token['refresh_token'];
            $this->openid = $token['openid'];
            $this->scope = $token['scope'];
        } else {
            throw new \Exception("get access token failed." . $token['errcode'], $token['errmsg']);
        }
        return $token;
    }

    function userInfo($token, $lang = "zh-CN")
    {
        $params = array();
        $params['access_token'] = isset($token['access_token']) ? $token['access_token'] : $this->access_token;
        $params['openid'] = isset($token['openid']) ? $token['openid'] : $this->openid;
        $params['lang'] = $lang;
        $response = $this->oAuthRequest($this->userInfoURL(), 'GET', $params);
        $user = json_decode($response, true);
        if (is_array($user) && !isset($user['errcode'])) {
            return $user;
        } else {
            return false;
        }
    }

    function oAuthRequest($url, $method, $parameters, $multi = false)
    {
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}";
        }
        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters))) {
                    $body = http_build_query($parameters);
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return $this->http($url, $method, $body, $headers);
        }
    }

    function http($url, $method, $postfields = NULL, $headers = array())
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

    function parseSignedRequest($signed_request)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);
        $sig = self::base64decode($encoded_sig);
        $data = json_decode(self::base64decode($payload), true);
        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
            return '-1';
        $expected_sig = hash_hmac('sha256', $payload, $this->appsecret, true);
        return ($sig !== $expected_sig) ? '-2' : $data;
    }

    function base64decode($str)
    {
        return base64_decode(strtr($str . str_repeat('=', (4 - strlen($str) % 4)), '-_', '+/'));
    }

    function getTokenFromArray($arr)
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

    function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    function post($url, $parameters = array(), $multi = false)
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    function delete($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    function getHeader($ch, $header)
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
