<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 05/10/2017
 * Time: 18:03
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class StringHelper
{

    public static function is_html($string)
    {
        return preg_match("/<[^<]+>/",$string,$m) != 0;
    }

    public static function getRealEffectiveLink($link) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $a = curl_exec($ch); // $a will contain all headers

        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        return $url;
    }

    public static function constains($haystack, $needles) {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
    public static function validateUrl($url) {

        if (self::constains($url, 'https')) {
            $url = str_replace('https', 'http', $url);
        }

        if (self::constains($url, 'http') == false) {
            $url = "http://".$url;
        }
        $html = file_get_contents($url);

        if (self::constains($html, '.ipa')) {
            return [
                'statue' => true
            ];
        } else {
            return [
                'statue' => false
            ];
        }

    }

    public static function getInbetweenStrings($start, $finish, $string) {
        $string   = " " . $string;
        $position = strpos($string, $start);
        if ($position == 0)
            return "";
        $position += strlen($start);
        $length = strpos($string, $finish, $position) - $position;
        return substr($string, $position, $length);
    }

    public static function randomAppleRequestId() {
        $request_id = md5(rand());
        $request_id = substr_replace($request_id, '-', 20, 0);
        $request_id = substr_replace($request_id, '-', 16, 0);
        $request_id = substr_replace($request_id, '-', 12, 0);
        $request_id = substr_replace($request_id, '-', 8, 0);
        return strtoupper($request_id);
    }

    public static function get_cookie($data) {
        preg_match('/^Set-Cookie: (.*?)$/mi', $data, $matches);
        if (count($matches) > 0) {
            return $matches[1];
        }
        return "No Connection";
    }
    public static function get_content($data) {
        $pos = strpos($data, '<?xml version="1.0" encoding="UTF-8"?>');
        return substr($data, $pos);
    }

    public static function validateAppName($string) {
        return preg_replace('/[^0-9A-Za-z\d\s]/', '', $string); // Removes special chars.
    }
    public static function getCertNameFromCertificateContent($contents) {
        $certificateNameToResign = StringHelper::getInbetweenStrings("iPhone Developer",")1", $contents);
        return "iPhone Developer".$certificateNameToResign.")";
    }
    public static function getDistCertNameFromCertificateContent($contents) {
        $certificateNameToResign = StringHelper::getInbetweenStrings("iPhone Distribution",")1", $contents);
        return "iPhone Distribution".$certificateNameToResign.")";
    }

    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}