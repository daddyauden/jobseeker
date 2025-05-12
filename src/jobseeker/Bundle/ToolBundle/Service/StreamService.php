<?php

namespace jobseeker\Bundle\ToolBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class StreamService
{

    const TIMEOUT = "timeout";
    const ZLIB = "compress.zlib://";
    const USERAGENT = 'JobSeeker App Engine Stream';
    const UNKNOWN_CODE = 0;

    private static $options = array();
    private static $defaultHttpContext = array(
        "follow_location" => 0,
        "ignore_errors" => 1,
    );
    private static $defaultSslContext = array(
        "verify_peer" => true,
    );

    public static function executeRequest($url, $method, $parameters)
    {
        $default_options = stream_context_get_options(stream_context_get_default());

        $requestHttpContext = array_key_exists('http', $default_options) ? $default_options['http'] : array();

        $requestHttpContext["content"] = http_build_query($parameters);

        $requestHttpContext["method"] = $method;

        $requestHttpContext["user_agent"] = self::USERAGENT;

        $requestSslContext = array_key_exists('ssl', $default_options) ? $default_options['ssl'] : array();

        $options = array(
            "http" => array_merge(self::$defaultHttpContext, $requestHttpContext),
            "ssl" => array_merge(self::$defaultSslContext, $requestSslContext)
        );

        $context = stream_context_create($options);

        @$fh = fopen($url, 'r', false, $context);

        $response_data = false;

        $respHttpCode = self::UNKNOWN_CODE;

        if ($fh) {
            if (isset(static::$options[self::TIMEOUT])) {
                stream_set_timeout($fh, self::$options[self::TIMEOUT]);
            }
            $response_data = stream_get_contents($fh);

            fclose($fh);
        }

        if (false === $response_data) {
            throw new \Exception(sprintf("HTTP Error: Unable to connect: '%s'", $respHttpCode), $respHttpCode);
        }

        return $response_data;
    }

}
