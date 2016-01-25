<?php

/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/20/16
 * Time: 6:43 PM
 */
class StadsService
{
    static function GetActiveStadsSession($username, $password)
    {
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        $ch = curl_init();

        // setup SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '');  //could be empty, but cause problems on some hosts
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');  //could be empty, but cause problems on some hosts

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        self::RedirectToLoginPage($ch);
        $SAMLResponse = self::SubmitUserData($ch, $username, $password);

        list($RelayState, $SAMLResponse) = self::GetRelayState($ch, $SAMLResponse);

        $selvbetjening = self::LoginToSTADS($ch, $SAMLResponse, $RelayState);
        curl_close($ch);

        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch2, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch2, CURLOPT_COOKIEJAR, '');  //could be empty, but cause problems on some hosts
        curl_setopt($ch2, CURLOPT_COOKIEFILE, '');  //could be empty, but cause problems on some hosts

        // receive server response ...
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch2, CURLOPT_HEADER, 1);
        curl_setopt($ch2, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array("Cookie: au_wayf_user=true; selvbetjening=$selvbetjening;"));
        curl_setopt($ch2, CURLOPT_VERBOSE, 1);
        curl_setopt($ch2, CURLOPT_URL, "https://sbstads.au.dk/sb_STAP/sb/resultater/studresultater.jsp");
        //curl_setopt($ch2, CURLOPT_COOKIE, "au_wayf_user=true; selvbetjening=$selvbetjening;");
        echo curl_exec($ch2) . "!";
        print_r(curl_getinfo($ch2));
        curl_close($ch2);
        //curl_setopt($ch, CURLOPT_URL, "https://sbstads.au.dk/sb_STAP/sb/common/velkommen.jsp");
        //echo curl_exec($ch);
        // remember to close curl
        // return the needed cookies instead
        return $ch;

        //curl_close($ch);
    }

    /**
     * @param $ch
     * @param $matches
     * @return mixed
     */
    private static function RedirectToLoginPage($ch)
    {
        // try to access STADS
        curl_setopt($ch, CURLOPT_URL, "https://sbstads.au.dk/sb_STAP/sb/index.jsp");
        $loginRedirect = curl_exec($ch);

        // fetch the login url and redirect to Single Sign On login screen
        preg_match('/content.+?url\W+?(.+?)\"/i', $loginRedirect, $matches);
        $loginUrl = $matches[1];
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_exec($ch);

        // simple redirect, SAMLRequest has changed
        curl_setopt($ch, CURLOPT_URL, curl_getinfo($ch)["redirect_url"]);
        curl_exec($ch);

        // redirect to the actual login page
        curl_setopt($ch, CURLOPT_URL, curl_getinfo($ch)["redirect_url"]);
        curl_exec($ch);
    }

    /**
     * @param $ch
     * @param $username
     * @param $password
     * @return array
     * @internal param $matches
     */
    private static function SubmitUserData($ch, $username, $password)
    {
        // enter our username and password and submit it
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array("username" => $username,
                "password" => $password)));
        $loginResult = curl_exec($ch);

        preg_match('/name="SAMLResponse" value="(.+)"/i', $loginResult, $matches);
        return $matches[1];
    }

    /**
     * @param $ch
     * @param $SAMLResponse
     * @param $matches
     * @return array
     */
    private static function GetRelayState($ch, $SAMLResponse)
    {
        curl_setopt($ch, CURLOPT_URL, "https://wayf.wayf.dk/module.php/saml/sp/saml2-acs.php/wayf.wayf.dk");
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array("SAMLResponse" => $SAMLResponse)));
        $samlResult = curl_exec($ch);

        preg_match('/name="RelayState" value="(.+)"/i', $samlResult, $matches);
        $RelayState = $matches[1];
        preg_match('/name="SAMLResponse" value="(.+)"/i', $samlResult, $matches);
        $SAMLResponse = $matches[1];
        return array($RelayState, $SAMLResponse);
    }

    /**
     * @param $ch
     * @param $SAMLResponse
     * @param $RelayState
     */
    private static function LoginToSTADS($ch, $SAMLResponse, $RelayState)
    {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            urldecode(http_build_query(array("SAMLResponse" => urlencode($SAMLResponse),
                "RelayState" => $RelayState))));
        curl_setopt($ch, CURLOPT_URL, "https://sbstads.au.dk:443/sb_STAP/saml/SAMLAssertionConsumer");
        curl_exec($ch);
        preg_match('/selvbetjening=(.+?)[;\n]/i', curl_getinfo($ch)["request_header"], $matches);
        return $matches[1];
    }
}