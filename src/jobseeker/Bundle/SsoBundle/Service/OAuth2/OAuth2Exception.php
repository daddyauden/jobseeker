<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2;

abstract class OAuth2Exception
{

    public static $err = array(
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
        "miss_code" => array(
            "code" => 103,
            "message" => "code is missing",
            "description" => "Missing parameter code is required"
        ),
        "expires_code" => array(
            "code" => 104,
            "message" => "authorization code is expired",
            "description" => "The authorization code has expired"
        ),
        "invalid_code" => array(
            "code" => 105,
            "message" => "client_secret is invalid",
            "description" => "Authorization code does not exist or is invalid"
        ),
        "invalid_client_secret" => array(
            "code" => 106,
            "message" => "client_secret is invalid",
            "description" => "client_secret is invalid or not registered"
        ),
        "miss_redirect_uri" => array(
            "code" => 107,
            "message" => "invalid_uri is missing",
            "description" => "redirect_uri is missing"
        ),
        "invalid_redirect_uri" => array(
            "code" => 108,
            "message" => "redirect_uri is invalid ",
            "description" => "redirect_uri is invalid or not registered"
        ),
        "mismatch_redirect_uri" => array(
            "code" => 109,
            "message" => "redirect_uri is mismatch",
            "description" => "The redirect_uri does not match"
        ),
        "invalid_response_type" => array(
            "code" => 110,
            "message" => "response type is invalid",
            "description" => "Invalid or missing response type"
        ),
        "invalid_grant_type" => array(
            "code" => 111,
            "message" => "grant type is invalid",
            "description" => "Invalid or grant type not supported"
        ),
        "invalid_scope" => array(
            "code" => 112,
            "message" => "scope is invalid",
            "description" => "An unsupported scope was requested or The scope requested is invalid for this client"
        ),
        "miss_scope" => array(
            "code" => 113,
            "message" => "scope is missing",
            "description" => "This application requires you specify a scope parameter"
        ),
        "miss_state" => array(
            "code" => 114,
            "message" => "state is required",
            "description" => "The state parameter is required"
        ),
        "access_deny" => array(
            "code" => 115,
            "message" => "access is deny",
            "description" => "deny access to the application"
        ),
        "invalid_method" => array(
            "code" => 116,
            "message" => "invalid request method",
            "description" => "The request method must be post"
        ),
        "many_method" => array(
            "code" => 117,
            "message" => "too many authenticate method",
            "description" => "Only one method may be used to authenticate at a time (Auth header, GET or POST)"
        ),
        "error_method" => array(
            "code" => 118,
            "message" => "authenticate method error",
            "description" => "error method for authenticate or When putting the token in the body, the method must be POST or PUT"
        ),
        "malformed_method" => array(
            "code" => 119,
            "message" => "authenticate malformed method",
            "description" => "authenticate malformed"
        ),
        "miss_grant_type" => array(
            "code" => 120,
            "message" => "grant_type is missing",
            "description" => "The grant type was not specified in the request"
        ),
        "invalid_grant_type" => array(
            "code" => 121,
            "message" => "invalid grant_type",
            "description" => "grant_type not supported"
        ),
        "invalid_token" => array(
            "code" => 122,
            "message" => "invalid token",
            "description" => "The access token provided is invalid"
        ),
        "expires_token" => array(
            "code" => 123,
            "message" => "access_token is expired",
            "description" => "The access token provided has expired"
        ),
        "miss_username" => array(
            "code" => 124,
            "message" => "miss username or parameter",
            "description" => "Missing parameters: username and password required'"
        ),
        "invalid_username" => array(
            "code" => 125,
            "message" => "username or password is invalid",
            "description" => "Invalid username and password combination"
        ),
        "error_username" => array(
            "code" => 126,
            "message" => "access_token is expired",
            "description" => "Unable to retrieve user information"
        ),
        "miss_refresh_token" => array(
            "code" => 127,
            "message" => "refresh_token is missing",
            "description" => "Missing parameter refresh_token is required"
        ),
        "expires_refresh_token" => array(
            "code" => 128,
            "message" => "refresh_token is expired",
            "description" => "The refresh_token has expired"
        ),
        "invalid_refresh_token" => array(
            "code" => 129,
            "message" => "refresh_token is invalid",
            "description" => "refresh_token invalid"
        ),
        "invalid_privilege" => array(
            "code" => 130,
            "message" => "the access_token require invalid privilege",
            "description" => "The request requires higher privileges than provided by the access token"
        ),
        "error_access_token" => array(
            "code" => 131,
            "message" => "get access_token error",
            "description" => "The request requires access_token error"
        ),
    );

    public static function get($message, $className = "", $line = "")
    {
        return array("error" => array_merge(self::$err[$message], array("class" => $className, "line" => $line)));
    }

}
