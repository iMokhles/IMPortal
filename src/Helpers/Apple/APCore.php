<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 09/10/2017
 * Time: 12:27
 */

namespace iMokhles\IMPortal\Helpers\Apple;

use CFPropertyList\CFPropertyList;
use CFPropertyList\CFTypeDetector;
use GuzzleHttp\Exception\RequestException;

use GuzzleHttp\Client;

class APCore
{

    /**
     * @var array
     * @access public
     */
    public $devAccount;

    /**
     * @var string
     * @access public
     */
    public $protocol_version_login = 'A1234';
    /**
     * @var string
     * @access public
     */
    public $user_locale = 'en_US';
    /**
     * @var string
     * @access public
     */
    public $machineName = 'StorePlus';
    /**
     * @var string
     * @access public
     */
    public $machineId = 'AP6P337C-3S63-4523-ASPE-6622234AASP8';
    /**
     * @var string
     * @access public
     */
    public $client_id = 'XABBG36SBA';

    /**
     * @var string
     * @access public
     */
    public $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";


    /**
     * @var string
     * @access public
     */
    public $loginBaseUrl = "https://idmsa.apple.com/IDMSWebAuth/";
    /**
     * @var string
     * @access public
     */
    public $listTeamBaseUrl = "https://developer.apple.com/services-account/QH65B2/account/";
    /**
     * @var string
     * @access public
     */
    public $servicesJsonsUrl = 'https://developer.apple.com/account/';

    /**
     * @var string
     * @access public
     */
    public $itunesconnect_olympus_v1 = "https://itunesconnect.apple.com/olympus/v1/";
    /**
     * @var string
     * @access public
     */
    public $oldBaseUrl = 'https://developerservices2.apple.com/services/QH65B2/';

    /**
     * @var string
     * @access public
     */
    public $app_id_key = "891bd3417a7776362562d2197f89480a8547b108fd934911bcbea0110d07f757";

    /**
     * @var Client
     * @access public
     */
    public $client;

    /**
     * @var string
     * @access public
     */
    public $myacinfo;

    /**
     * @var string
     * @access public
     */
    public $teamId;

    /**
     * @var string
     * @access public
     */
    public $accountEmail;

    /**
     * @var string
     * @access public
     */
    public $csrf;

    /**
     * @var string
     * @access public
     */
    public $csrf_ts;

    /**
     * @var string
     * @access public
     */
    private $storagePath;

    /**
     * @var RequestHelper
     */
    public $requestHelper;

    /**
     * APCore constructor.
     * @param $account
     * @param $storagePath
     */
    public function __construct($account, $storagePath) {

        $this->devAccount = $account;
        $this->storagePath = $storagePath;
        $this->requestHelper = new RequestHelper($this->storagePath);
    }

    /**
     * @param array $params
     * @param array $options
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performLoginRequest($params = [], $options = []) {

        try {
            $this->client = new Client(['base_uri' => $this->loginBaseUrl]);
            $this->client->request("POST", "authenticate", array_merge($options, [
                'query' => array_merge($params, [
                    'appIdKey' => $this->app_id_key,
                ]),
                'verify' => true,
                'cookies' => $this->setCookiesJarFile(),
                'headers' => [
                    'X-Requested-With' =>'XMLHttpRequest',
                    'X-Apple-Widget-Key' => $this->requestHelper->getServiceKey(),
                    'User-Agent' => $this->userAgent,
                    'Accept-Language' => 'en-us',
                    'Connection' => 'keep-alive',
                ]
            ]));
            return true;
        } catch (RequestException $e) {
            // Log exception here
            return false;
        }

    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performLogin() {
        $this->performLoginRequest([
            'appleId' => $this->devAccount['email'],
            'accountPassword' => $this->devAccount['password']
        ]);

    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performAuth() {
        $this->performLogin();
        $this->performSessionRequest();
        $responseTeams = $this->performListTeamsRequest();
        $responseArray = json_decode($responseTeams->getBody(), true);
        $teams = $responseArray['teams'];
        if (count($teams) > 0) {

            $this->getMyAcInfoFromJson();

            $this->teamId = $teams[0]['teamId'];

            $responseHeader = $responseTeams->getHeaders();

            if (array_key_exists('csrf', $responseHeader)) {
                $this->csrf = $responseHeader['csrf'];
                $this->csrf_ts = $responseHeader['csrf_ts'];
            }

        }

        $responseProfile = $this->performUserProfileRequest();

        $responseHeader = $responseProfile->getHeaders();

        if (array_key_exists('csrf', $responseHeader)) {
            $this->csrf = $responseHeader['csrf'];
            $this->csrf_ts = $responseHeader['csrf_ts'];
        }

        $this->accountEmail = $this->devAccount['email'];

    }

    /**
     * @param array $params
     * @param array $options
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performAcceptAgreementsRequest($params = [], $options = []) {

        try {
            $this->client = new Client(['base_uri' => $this->listTeamBaseUrl]);
            $response =$this->client->request("POST", "acceptAgreements", array_merge($options, [
                'json' => array_merge($params, [

                ]),
                'verify' => true,
                'cookies' => $this->getCookiesJarFile()
            ]));

            $data = $response;
            $validateResult = json_decode($data->getBody(), true);
            if ( $validateResult['resultCode'] != 0 ) {
                if ( $validateResult['resultCode'] != 0 ) {
                    return false;
                }
            }

            if ( $validateResult['resultCode'] == 0 ) {
                return true;
            }
        } catch (RequestException $e) {
            return false;
        }

    }

    /**
     * @param array $params
     * @param array $options
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performListTeamsRequest($params = [], $options = []) {

        try {
            $this->client = new Client(['base_uri' => $this->listTeamBaseUrl]);
            $response =$this->client->request("POST", "getTeams", array_merge($options, [
                'query' => array_merge($params, [

                ]),
                'verify' => true,
                'cookies' => $this->getCookiesJarFile()
            ]));

            $data = $response;
            return $data;
        } catch (RequestException $e) {
            return false;
        }

    }

    /**
     * @param array $params
     * @param array $options
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performUserProfileRequest($params = [], $options = []) {

//        $this->performLogin();
        try {
            $this->client = new Client(['base_uri' => $this->listTeamBaseUrl]);
            $response =$this->client->request("POST", "getUserProfile", array_merge($options, [
                'query' => array_merge($params, [

                ]),
                'verify' => true,
                'cookies' => $this->getCookiesJarFile()
            ]));

            $data = $response;
            return $data;
        } catch (RequestException $e) {
            return false;
        }

    }

    /**
     * @param string $method
     * @param array $params
     * @param array $options
     * @return bool|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performSessionRequest($method = "GET", $params = [], $options = []) {

//        $this->performLogin();

        try {
            $this->client = new Client(['base_uri' => $this->itunesconnect_olympus_v1]);
            $response =$this->client->request($method, "session", array_merge($options, [
                'query' => array_merge($params, [

                ]),
                'verify' => true,
                'cookies' => $this->setCookiesJarFile()
            ]));

            $data = $response->getBody();
            return $data;
        } catch (RequestException $e) {
            return false;
        }

    }

    /**
     * @param $uri
     * @param string $method
     * @param array $options
     * @param array $params
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performRequest($uri, $method = "POST", $options = [], $params = []) {

        $this->performAuth();

        try {
            $queryOptions = null;
            if (count($params) == 0) {
                $queryOptions = array_merge([
                    'content-type' => 'text/x-url-arguments',
                    'accept' => 'application/json',
                    'requestId' => StringHelper::randomAppleRequestId(),
                    'userLocale' => $this->user_locale,
                    'teamId' => $this->teamId,
                ]);
            } else {
                $queryOptions = array_merge([
                    'content-type' => 'text/x-url-arguments',
                    'accept' => 'application/json',
                    'requestId' => StringHelper::randomAppleRequestId(),
                    'userLocale' => $this->user_locale,
                    'teamId' => $this->teamId,
                ], $params);
            }
            $this->client = new Client(['base_uri' => $this->listTeamBaseUrl]);
            $request = $this->client->request($method, $uri, array_merge($options, [
                'query' => $queryOptions,
                'headers' => [
                    'X-Requested-With' =>'XMLHttpRequest',
                    'X-Apple-Widget-Key' => $this->requestHelper->getServiceKey(),
                    'Cookie' => $this->myacinfo,
                    'User-Agent' => $this->userAgent,
                    'Accept-Language' => 'en-us',
                    'Connection' => 'keep-alive',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept' => '*/*',
                    'csrf' => $this->csrf,
                    'csrf_ts' => $this->csrf_ts
                ],
                'verify' => true,
                'cookies' => $this->getCookiesJarFile(),
            ]));



            $responseHeader = $request->getHeaders();

            if (array_key_exists('csrf', $responseHeader)) {
                $this->csrf = $responseHeader['csrf'];
                $this->csrf_ts = $responseHeader['csrf_ts'];
            }

            return $request;
        } catch (RequestException $e) {
            return false;
        }

    }

    /**
     * @param $uri
     * @param $method
     * @param $options
     * @param array $newQuery
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performRequestForJson($uri, $method, $options, $newQuery = []) {

        $this->performAuth();

        try {

            $queryOptions = null;
            if (count($newQuery) == 0) {
                $queryOptions = array_merge([
                    'content-type' => 'text/x-url-arguments',
                    'accept' => 'application/json',
                    'requestId' => StringHelper::randomAppleRequestId(),
                    'userLocale' => $this->user_locale,
                    'teamId' => $this->teamId,
                ]);
            } else {
                $queryOptions = array_merge([
                    'content-type' => 'text/x-url-arguments',
                    'accept' => 'application/json',
                    'requestId' => StringHelper::randomAppleRequestId(),
                    'userLocale' => $this->user_locale,
                    'teamId' => $this->teamId,
                ], $newQuery);
            }
            $this->client = new Client(['base_uri' => $this->servicesJsonsUrl]);
            $request = $this->client->request($method, $uri, array_merge($options, [
                'query' => $queryOptions,
                'headers' => [
                    'X-Requested-With' =>'XMLHttpRequest',
                    'X-Apple-Widget-Key' => $this->requestHelper->getServiceKey(),
                    'Cookie' => $this->myacinfo,
                    'User-Agent' => $this->userAgent,
                    'Accept-Language' => 'en-us',
                    'Connection' => 'keep-alive',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept' => '*/*',
                    'csrf' => $this->csrf,
                    'csrf_ts' => $this->csrf_ts,

                ],
                'verify' => true,
                'cookies' => $this->getCookiesJarFile(),
            ]));

            return $request;

        } catch (RequestException $e) {
            return false;
        }

    }

    /**
     * @param string $method
     * @param $uri
     * @param array $params
     * @param array $options
     * @param array $newQuery
     * @return bool|mixed
     * @throws \CFPropertyList\IOException
     * @throws \CFPropertyList\PListException
     * @throws \DOMException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performOldRequest($method = "POST", $uri, $params = [], $options = [], $newQuery = []) {

        $this->performAuth();

        try {

            $payload = array_merge($params, [
                'client' => 'Xcode',
                'requestId' => StringHelper::randomAppleRequestId(),
                'userLocale' => $this->user_locale,
                'protocolVersion' => $this->protocol_version_login,
                'DTDK_Platform' => 'ios',
                'machineId' => $this->machineId,
                'machineName' => $this->machineName,
                'teamId' => $this->teamId
            ]);

            $plist = new CFPropertyList();
            $type_detector = new CFTypeDetector();
            $plist->add($type_detector->toCFType($payload));
            $contents = $plist->toXML(true);


            $this->client = new Client(['base_uri' => $this->oldBaseUrl ]);
            $response =$this->client->request($method, $uri, array_merge($options, [
                'query' => array_merge($newQuery, [
                    'clientId' => $this->client_id
                ]),
                'body' => $contents,
                'verify' => true,
                'cookies' => $this->getCookiesJarFile(),
                'headers' => [
                    'User-Agent' => 'Xcode',
                    'X-Xcode-Version' => '8.0 (8A218a)',
                    'Accept-Language' => 'en-us',
                    'Accept' => 'text/x-xml-plist',
                    'Connection' => 'close',
                    'Content-Type' => 'text/x-xml-plist',
                    'Cookie' => $this->myacinfo

                ],
            ]));

            $data = $response->getBody();
            $content = $this->get_content($data);
            $plist = new CFPropertyList();
            $plist->parse($content, CFPropertyList::FORMAT_AUTO);
            $plistData = $plist->toArray();

            return $plistData;
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param string $data
     * @return mixed
     */
    private function get_content($data) {
        $pos = strpos($data, '<?xml version="1.0" encoding="UTF-8"?>');
        return substr($data, $pos);
    }

    /**
     * @return string
     */
    public function getMyAcInfoFromJson() {

        $filePath = "cookies/account_id_{$this->devAccount['id']}.json";
        $cookieFile = $this->requestHelper->getStorage()->getStoragePath($filePath);
        if (file_exists($cookieFile)) {

            $json = file_get_contents($cookieFile);
            $data = json_decode($json, true);
            if (is_array($data)) {
                foreach ($data as $cookie) {
                    $name = $cookie["Name"];
                    if ($name === "myacinfo") {
                        $value = $cookie["Value"];
                        $this->myacinfo = "myacinfo=".$value;
                    }
                }
            }
        }
        return "myacinfo=";
    }

    /**
     * @return string
     */
    public function getCookiesFile() {

        $filePath = "cookies/account_id_{$this->devAccount['id']}.json";
        $cookieFile = $this->requestHelper->getStorage()->getStoragePath($filePath);
        if (file_exists($cookieFile) == false) {
            $this->requestHelper->getStorage()->putFileAs("", $filePath);
        }
        return $cookieFile;
    }

    /**
     * @return IMFileCookieJar
     */
    public function setCookiesJarFile() {
        return new IMFileCookieJar($this->getCookiesFile(), true, true);
    }

    /**
     * @return IMFileCookieJar
     */
    public function getCookiesJarFile() {
        return new IMFileCookieJar($this->getCookiesFile(), true, false);
    }

}