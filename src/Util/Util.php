<?php

namespace Util;

/**
 * Esta clase contiene funciones comunes usadas en diferentes partes del proyecto
 * @author Cesar Giraldo <cesargiraldo1108@gmail.com> 23/12/2015
 */
class Util {

    public static function getCurrentDate($zone = 'America/Bogota') {
        $timezone = new \DateTimeZone($zone);
        $datetime = new \DateTime('now');
        $datetime->setTimezone($timezone);
        return $datetime;
    }

    public static function getRandomCode($size = 5) {
        $alphabet = "abcdefghijkmnpqrstuwxyz1234567890";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

        for ($i = 0; $i < $size; $i++) {
            $randPosition = mt_rand(0, $alphaLength);
            $pass[] = $alphabet[$randPosition];
        }
        return implode($pass); //turn the array into a string
    }

    public static function getYearstoForm($number) {
        $currentYear = (new \DateTime('now'))->format('Y') - 1;
        $years = array();
        for ($i = 0; $i < $number; $i++) {
            array_push($years, $currentYear);
            $currentYear++;
        }
        return $years;
    }

}
