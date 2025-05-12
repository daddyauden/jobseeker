<?php

namespace jobseeker\Bundle\CareerBundle\Controller;

use jobseeker\Bundle\ToolBundle\Base;

class PaymentController extends Base
{

    public function indexAction()
    {
        return $this->render('career/payment/index.html.twig');
    }

    public function paymentAction($type)
    {
        return $this->render('career/payment/payment.html.twig', array('type' => strtolower($type)));
    }

    public function dopaymentAction()
    {
        require_once 'CallerService.php';
        /**
         * Get required parameters from the web form for the request
         */
        $paymentType = urlencode($_POST['paymentType']);
        $firstName = urlencode($_POST['firstName']);
        $lastName = urlencode($_POST['lastName']);
        $creditCardType = urlencode($_POST['creditCardType']);
        $creditCardNumber = urlencode($_POST['creditCardNumber']);
        $expDateMonth = urlencode($_POST['expDateMonth']);

        // Month must be padded with leading zero
        $padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);

        $expDateYear = urlencode($_POST['expDateYear']);
        $cvv2Number = urlencode($_POST['cvv2Number']);
        $address1 = urlencode($_POST['address1']);
        $address2 = urlencode($_POST['address2']);
        $city = urlencode($_POST['city']);
        $state = urlencode($_POST['state']);
        $zip = urlencode($_POST['zip']);
        $amount = urlencode($_POST['amount']);
        //$currencyCode=urlencode($_POST['currency']);
        $currencyCode = "USD";
        $paymentType = urlencode($_POST['paymentType']);

        /**
         *  Construct the request string that will be sent to PayPal.
         * The variable $nvpstr contains all the variables and is a
         * name value pair string with & as a delimiter 
         */
        $nvpstr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber&EXPDATE=" . $padDateMonth . $expDateYear . "&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&STREET=$address1&CITY=$city&STATE=$state" .
            "&ZIP=$zip&COUNTRYCODE=US&CURRENCYCODE=$currencyCode";

        /**
         * Make the API call to PayPal, using API signature.
         * The API response is stored in an associative array called $resArray
         */
        $resArray = hash_call("doDirectPayment", $nvpstr);

        /**
         *  Display the API response back to the browser.
         * If the response from PayPal was a success, display the response parameters'
         * If the response was an error, display the errors received using APIError.php.
         */
        $ack = strtoupper($resArray["ACK"]);

        if ($ack != "SUCCESS") {
            $_SESSION['reshash'] = $resArray;
            $location = "APIError.php";
            header("Location: $location");
        }
        return $this->render('career/payment/dopayment.html.twig');
    }

    public function checkoutAction($type)
    {
        return $this->render('career/payment/checkout.html.twig');
    }

    public function docheckoutAction()
    {
        return $this->render('career/payment/docheckout.html.twig');
    }

}
