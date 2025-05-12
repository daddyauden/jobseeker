<?php

namespace jobseeker\Bundle\ToolBundle\Service;

class CurlService
{

    const FORMAT = 'json';
    const DECODE_JSON = TRUE;
    const HOST = "http://sso.jobseeker.com";

    private static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 60,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'JobSeeker App Engine Curl',
    );
    protected static $boundary = '';

    public static function get($url, $parameters = array())
    {
        $response = self::httpRequest($url, 'GET', $parameters);
        if (self::FORMAT === 'json' && self::DECODE_JSON) {
            return json_decode($response, true);
        }

        return $response;
    }

    public static function post($url, $parameters = array(), $multi = false)
    {
        $response = self::httpRequest($url, 'POST', $parameters, $multi);
        if (self::FORMAT === 'json' && self::DECODE_JSON) {
            return json_decode($response, true);
        }
        return $response;
    }

    private static function httpRequest($url, $method, $parameters, $multi = false)
    {

        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = self::HOST . $url . self::FORMAT;
        }

        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return self::http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters))) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return self::http($url, $method, $body, $headers);
        }
    }

    private static function http($url, $method, $postfields = NULL)
    {
        $ch = curl_init();
        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
        $opts[CURLOPT_SSL_VERIFYPEER] = FALSE;
        $opts[CURLOPT_FOLLOWLOCATION] = FALSE;
        $opts[CURLOPT_RETURNTRANSFER] = TRUE;
        $opts[CURLINFO_HEADER_OUT] = TRUE;
        $opts[CURLOPT_HEADER] = FALSE;
        $opts[CURLOPT_ENCODING] = "";
        if (version_compare(phpversion(), '5.4.0', '<')) {
            $opts[CURLOPT_SSL_VERIFYHOST] = 1;
        } else {
            $opts[CURLOPT_SSL_VERIFYHOST] = 2;
        }
        switch ($method) {
            case 'POST':
                $opts[CURLOPT_POST] = TRUE;
                if (!empty($postfields)) {
                    $opts[CURLOPT_POSTFIELDS] = $postfields;
                }
                break;
            case 'DELETE':
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
        $opts[CURLOPT_URL] = $url;
        $opts[CURLINFO_HEADER_OUT] = TRUE;
        curl_setopt_array($ch, $opts);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new \Exception(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        return $result;
    }

    private static function build_http_query_multi($params)
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
