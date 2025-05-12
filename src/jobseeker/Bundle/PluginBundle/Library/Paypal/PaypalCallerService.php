<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection\PaymentPlugin\Paypal;

class PaypalCallerService
{
    const API_USERNAME = 'platfo_1255077030_biz_api1.gmail.com';
    const API_PASSWORD = '1255077037';
    const API_SIGNATURE = 'Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf';
    const API_ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';
    const VERSION = '65.1';
    const SUBJECT = '';
    const AUTH_MODE = '';
    const AUTH_TOKEN = '4oSymRbHLgXZVIvtZuQziRVVxcxaiRpOeOEmQw';
    const AUTH_SIGNATURE = '+q1PggENX0u+6vj+49tLiw9CLpA=';
    const AUTH_TIMESTAMP = '1284959128P';
    const USE_PROXY = false;
    const PROXY_HOST = '127.0.0.1';
    const PROXY_PORT = '808';
    const PAYPAL_URL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    const ACK_SUCCESS = 'SUCCESS';
    const ACK_SUCCESS_WITH_WARNING = 'SUCCESSWITHWARNING';

    public static function nvpHeader()
    {
        $nvpHeaderStr = "";
        if (!self::AUTH_MODE) {
            $AuthMode = "AUTH_MODE";
        } else {
            if (self::API_USERNAME && self::API_PASSWORD && self::API_SIGNATURE && self::SUBJECT) {
                $AuthMode = "THIRDPARTY";
            } else if (self::API_USERNAME && self::API_PASSWORD && self::API_SIGNATURE) {
                $AuthMode = "3TOKEN";
            } elseif (self::AUTH_TOKEN && self::AUTH_SIGNATURE && self::AUTH_TIMESTAMP) {
                $AuthMode = "PERMISSION";
            } elseif (self::SUBJECT) {
                $AuthMode = "FIRSTPARTY";
            }
        }
        switch ($AuthMode) {
            case "3TOKEN" :
                $nvpHeaderStr = "&PWD=" . urlencode(self::API_PASSWORD) . "&USER=" . urlencode(self::API_USERNAME) . "&SIGNATURE=" . urlencode(self::API_SIGNATURE);
                break;
            case "FIRSTPARTY" :
                $nvpHeaderStr = "&SUBJECT=" . urlencode(self::SUBJECT);
                break;
            case "THIRDPARTY" :
                $nvpHeaderStr = "&PWD=" . urlencode(self::API_PASSWORD) . "&USER=" . urlencode(self::API_USERNAME) . "&SIGNATURE=" . urlencode(self::API_SIGNATURE) . "&SUBJECT=" . urlencode(self::SUBJECT);
                break;
            case "PERMISSION" :
                $nvpHeaderStr = self::formAutorization(self::AUTH_TOKEN, self::AUTH_SIGNATURE, self::AUTH_TIMESTAMP);
                break;
        }
        return $nvpHeaderStr;
    }

    /**
     * hash_call: Function to perform the API call to PayPal using API signature
     * @methodName is name of API  method.
     * @nvpStr is nvp string.
     * returns an associtive array containing the response from the server.
     */
    public static function hash_call($methodName, $nvpStr)
    {
        $nvpheader = self::nvpHeader();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (self::AUTH_MODE && self::AUTH_SIGNATURE && self::AUTH_TIMESTAMP) {
            $headers_array[] = "X-PP-AUTHORIZATION: " . $nvpheader;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
            curl_setopt($ch, CURLOPT_HEADER, false);
        } else {
            $nvpStr = $nvpheader . $nvpStr;
        }
        if (self::USE_PROXY) {
            curl_setopt($ch, CURLOPT_PROXY, self::PROXY_HOST . ":" . self::PROXY_PORT);
        }
        if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
            $nvpStr = "&VERSION=" . urlencode(self::VERSION) . $nvpStr;
        }
        $nvpreq = "METHOD=" . urlencode($methodName) . $nvpStr;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        $response = curl_exec($ch);
        $nvpResArray = self::deformatNVP($response);
        $nvpReqArray = self::deformatNVP($nvpreq);
        $_SESSION['nvpReqArray'] = $nvpReqArray;
        if (curl_errno($ch)) {
            $_SESSION['curl_error_no'] = curl_errno($ch);
            $_SESSION['curl_error_msg'] = curl_error($ch);
            $location = "APIError.php";
            header("Location: $location");
        } else {
            curl_close($ch);
        }
        return $nvpResArray;
    }

    /** This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     * @nvpstr is NVPString.
     * @nvpArray is Associative Array.
     */
    public static function deformatNVP($nvpstr)
    {
        $intial = 0;
        $nvpArray = array();
        while (strlen($nvpstr)) {
            $keypos = strpos($nvpstr, '=');
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }

    public static function formAutorization($auth_token, $auth_signature, $auth_timestamp)
    {
        $authString = "token=" . $auth_token . ",signature=" . $auth_signature . ",timestamp=" . $auth_timestamp;
        return $authString;
    }

}
