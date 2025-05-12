<?php

namespace jobseeker\Bundle\SsoBundle\Controller;

class ApiException
{

    private static $err = array(
        "miss_client_id" => array(
            "code" => 100,
            "message" => "client_id is missing",
            "description" => "client_id is missing"
        ),
        "invalid_client_id" => array(
            "code" => 101,
            "message" => "client_id is invalid",
            "description" => "client_id is invalid or not registered"
        ),
        "miss_client_secret" => array(
            "code" => 102,
            "message" => "client_secret is missing",
            "description" => "client_secret is missing"
        ),
        "invalid_client_secret" => array(
            "code" => 103,
            "message" => "client_secret is invalid",
            "description" => "client_secret is invalid or not registered"
        ),
        "invalid_method" => array(
            "code" => 104,
            "message" => "invalid request method",
            "description" => "The request method must be post"
        ),
        "miss_parameter" => array(
            "code" => 105,
            "message" => "parameter is missing",
            "description" => "Missing request parameter"
        ),
        "user_exist" => array(
            "code" => 400,
            "message" => "user exist",
            "description" => "user exist in sso system"
        ),
        "user_register_error" => array(
            "code" => 401,
            "message" => "register user error",
            "description" => "register user error"
        )
    );

    static public function getMessage($message)
    {
        return array("error" => self::$err[$message]);
    }

}
