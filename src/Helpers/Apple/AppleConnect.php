<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 08/10/2017
 * Time: 01:20
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class AppleConnect
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
    private $storagePath;

    /**
     * @var APCore
     * @access public
     */
    public $requestCore;

    /**
     * AppleConnect constructor.
     * @param $account
     * @param $storagePath
     */
    public function __construct($account, $storagePath) {

        $this->storagePath = $storagePath;
        $this->requestCore = new APCore($account, $this->storagePath);
        $this->devAccount = $account;
    }

    /**
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isAccountActive() {
        $this->requestCore->performLogin();
        $responseTeams = $this->requestCore->performListTeamsRequest();
        $responseArray = json_decode($responseTeams->getBody(), true);
        $teams = $responseArray['teams'];
        if (is_array($teams)) {
            if (count($teams) > 0) {
                return true;
            }
        }

        return false;
    }
}