<?php

/**
 * Created by PhpStorm.
 * User: dklukjor
 * Date: 1/25/16
 * Time: 3:28 PM
 */
class ScheduleService
{
    private $ch;
    private $cardNumber;

    function __construct($cardNumber)
    {
        $this->cardNumber = $cardNumber;
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    }

    function __destruct()
    {
        curl_close($this->ch);
    }

    function GetScheduleJSON() {
        curl_setopt($this->ch, CURLOPT_URL, "http://lukasj.org/auskema/$this->cardNumber/json");
        return curl_exec($this->ch);
    }
}