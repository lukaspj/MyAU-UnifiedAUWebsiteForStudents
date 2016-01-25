<?php

/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/20/16
 * Time: 6:43 PM
 */
class StadsService
{
    private $ch;

    function __construct($username, $password) {
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        $this->ch = curl_init();

        // setup SSL
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, '');  //could be empty, but cause problems on some hosts
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, '');  //could be empty, but cause problems on some hosts

        // receive server response ...
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $this->RedirectToLoginPage();
        $SAMLResponse = $this->SubmitUserData($username, $password);

        list($RelayState, $SAMLResponse) = $this->GetRelayState($SAMLResponse);

        $this->LoginToSTADS($SAMLResponse, $RelayState);
    }

    function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * @param $this->ch
     * @param $matches
     * @return mixed
     */
    private function RedirectToLoginPage()
    {
        // try to access STADS
        curl_setopt($this->ch, CURLOPT_URL, "https://sbstads.au.dk/sb_STAP/sb/index.jsp");
        $loginRedirect = curl_exec($this->ch);

        // fetch the login url and redirect to Single Sign On login screen
        preg_match('/content.+?url\W+?(.+?)\"/i', $loginRedirect, $matches);
        $loginUrl = $matches[1];
        curl_setopt($this->ch, CURLOPT_URL, $loginUrl);
        curl_exec($this->ch);

        // simple redirect, SAMLRequest has changed
        $curlInfo = curl_getinfo($this->ch);
        curl_setopt($this->ch, CURLOPT_URL, $curlInfo["redirect_url"]);
        curl_exec($this->ch);

        // redirect to the actual login page
        $curlInfo = curl_getinfo($this->ch);
        curl_setopt($this->ch, CURLOPT_URL, $curlInfo["redirect_url"]);
        curl_exec($this->ch);
    }

    /**
     * @param $this->ch
     * @param $username
     * @param $password
     * @return array
     * @internal param $matches
     */
    private function SubmitUserData($username, $password)
    {
        // enter our username and password and submit it
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,
            http_build_query(array("username" => $username,
                "password" => $password)));
        $loginResult = curl_exec($this->ch);

        preg_match('/name="SAMLResponse" value="(.+)"/i', $loginResult, $matches);
        return $matches[1];
    }

    /**
     * @param $this->ch
     * @param $SAMLResponse
     * @param $matches
     * @return array
     */
    private function GetRelayState($SAMLResponse)
    {
        curl_setopt($this->ch, CURLOPT_URL, "https://wayf.wayf.dk/module.php/saml/sp/saml2-acs.php/wayf.wayf.dk");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,
            http_build_query(array("SAMLResponse" => $SAMLResponse)));
        $samlResult = curl_exec($this->ch);

        preg_match('/name="RelayState" value="(.+)"/i', $samlResult, $matches);
        $RelayState = $matches[1];
        preg_match('/name="SAMLResponse" value="(.+)"/i', $samlResult, $matches);
        $SAMLResponse = $matches[1];
        return array($RelayState, $SAMLResponse);
    }

    /**
     * @param $this->ch
     * @param $SAMLResponse
     * @param $RelayState
     */
    private function LoginToSTADS($SAMLResponse, $RelayState)
    {
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,
            urldecode(http_build_query(array("SAMLResponse" => urlencode($SAMLResponse),
                "RelayState" => $RelayState))));
        curl_setopt($this->ch, CURLOPT_URL, "https://sbstads.au.dk:443/sb_STAP/saml/SAMLAssertionConsumer");
        curl_exec($this->ch);
        $curlInfo = curl_getinfo($this->ch);
        preg_match('/selvbetjening=(.+?)[;\n]/i', $curlInfo["request_header"], $matches);
        return $matches[1];
    }

    public function GetResultPage()
    {
        curl_setopt($this->ch, CURLOPT_URL, "https://sbstads.au.dk/sb_STAP/sb/resultater/studresultater.jsp");
        return curl_exec($this->ch);
    }

    public function GetStudiesPage()
    {
        curl_setopt($this->ch, CURLOPT_URL, "https://sbstads.au.dk/sb_STAP/sb/indskrivning/visIndskrivning.jsp");
        return curl_exec($this->ch);
    }
}