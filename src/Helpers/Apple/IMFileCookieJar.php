<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 22:46
 */

namespace iMokhles\IMPortal\Helpers\Apple;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class IMFileCookieJar extends CookieJar
{

    /** @var string filename */
    private $filename;

    /** @var bool Control whether to persist session cookies or not. */
    private $storeSessionCookies;

    /** @var bool Control whether to save to filename or not. */
    private $shouldSave;

    /**
     * Create a new FileCookieJar object
     *
     * @param string $cookieFile        File to store the cookie data
     * @param bool $storeSessionCookies Set to true to store session cookies
     *                                  in the cookie jar.
     * @param string $shouldSave        Set to true to save to filename
     *
     * @throws \RuntimeException if the file cannot be found or created
     */
    public function __construct($cookieFile, $storeSessionCookies = false, $shouldSave)
    {
        $this->filename = $cookieFile;
        $this->storeSessionCookies = $storeSessionCookies;
        $this->shouldSave = $shouldSave;

        if (file_exists($cookieFile)) {
            $this->load($cookieFile);
        }
    }

    /**
     * Saves the file when shutting down
     */
    public function __destruct()
    {
        if ($this->shouldSave == true) {
            $this->save($this->filename);
        }
    }

    /**
     * Saves the cookies to a file.
     *
     * @param string $filename File to save
     * @throws \RuntimeException if the file cannot be found or created
     */
    public function save($filename)
    {
        $json = [];
        foreach ($this as $cookie) {
            /** @var SetCookie $cookie */
            if (CookieJar::shouldPersist($cookie, $this->storeSessionCookies)) {
                $json[] = $cookie->toArray();
            }
        }

        $jsonStr = json_encode($json, JSON_PRETTY_PRINT);
        if ($this->storeSessionCookies == true) {
//            \Log::info("JSON: ".$jsonStr);

        }
        if (false === file_put_contents($filename, $jsonStr)) {
            throw new \RuntimeException("Unable to save file {$filename}");
        }
    }

    /**
     * Load cookies from a JSON formatted file.
     *
     * Old cookies are kept unless overwritten by newly loaded ones.
     *
     * @param string $filename Cookie file to load.
     * @throws \RuntimeException if the file cannot be loaded.
     */
    public function load($filename)
    {
        $json = file_get_contents($filename);
        if (false === $json) {
            throw new \RuntimeException("Unable to load file {$filename}");
        } elseif ($json === '') {
            return;
        }

        $data = json_decode($json, true);
        if (is_array($data)) {
            foreach (json_decode($json, true) as $cookie) {
                $this->setCookie(new SetCookie($cookie));
            }
        } elseif (strlen($data)) {
            throw new \RuntimeException("Invalid cookie file: {$filename}");
        }
    }
}