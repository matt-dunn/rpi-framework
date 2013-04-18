<?php

namespace RPI\Framework\Helpers;

/**
 * Password helpers
 * @author Matt Dunn
 */
class Password
{
    private function __construct()
    {
    }

    /**
     * Generate a random password
     * @param  integer $length Length of password required
     * @return string  Random password
     */
    public static function generateRandomPassword($length = 8)
    {
        // Based on http://wiki.jumba.com.au/wiki/PHP_Generate_random_password
        $password = "";
        // The letter l (lowercase L) and the number 1 have been removed from
        // the possible options as they are commonly mistaken for each other.
        $possible = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $i = 0;

        while ($i < $length) {
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }

        return $password;
    }

    public static function generateSalt($maxLength = 64)
    {
        $characterList = "!$%^&*()_+-,.@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $salt = "";
        for ($i = 0; $i < $maxLength; $i++) {
            $salt .= $characterList{mt_rand(0, strlen($characterList) - 1)};
        }
        $i = 0;

        return $salt;
    }

    /**
     *
     * @param  string  $password Plain text password
     * @return integer Percentage password strength (100 = strong)
     */
    public static function checkPasswordStrength($password)
    {
        $strength = 0;
        $password_length = strlen($password);

        if ($password_length < 4) {
            return $strength;
        } else {
            $strength = $password_length * 4;
        }

        for ($i = 2; $i <= 4; $i++) {
            $temp = str_split($password, $i);

            $strength -= (ceil($password_length / $i) - count(array_unique($temp)));
        }

        preg_match_all('/[0-9]/', $password, $numbers);

        if (!empty($numbers)) {
            $numbers = count($numbers[0]);

            if ($numbers >= 3) {
                $strength += 5;
            }
        } else {
            $numbers = 0;
        }

        preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^¨\\\]/', $password, $symbols);

        if (!empty($symbols)) {
            $symbols = count($symbols[0]);

            if ($symbols >= 2) {
                $strength += 5;
            }
        } else {
            $symbols = 0;
        }

        preg_match_all('/[a-z]/', $password, $lowercase_characters);
        preg_match_all('/[A-Z]/', $password, $uppercase_characters);

        if (!empty($lowercase_characters)) {
            $lowercase_characters = count($lowercase_characters[0]);
        } else {
            $lowercase_characters = 0;
        }

        if (!empty($uppercase_characters)) {
            $uppercase_characters = count($uppercase_characters[0]);
        } else {
            $uppercase_characters = 0;
        }

        if (($lowercase_characters > 0) && ($uppercase_characters > 0)) {
            $strength += 10;
        }

        $characters = $lowercase_characters + $uppercase_characters;

        if (($numbers > 0) && ($symbols > 0)) {
            $strength += 15;
        }

        if (($numbers > 0) && ($characters > 0)) {
            $strength += 15;
        }

        if (($symbols > 0) && ($characters > 0)) {
            $strength += 15;
        }

        if (($numbers == 0) && ($symbols == 0)) {
            $strength -= 10;
        }

        if (($symbols == 0) && ($characters == 0)) {
            $strength -= 10;
        }

        if ($strength < 0) {
            $strength = 0;
        }

        if ($strength > 100) {
            $strength = 100;
        }

        return $strength;
    }
}
