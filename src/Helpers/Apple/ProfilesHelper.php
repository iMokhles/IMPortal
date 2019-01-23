<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 23:41
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class ProfilesHelper extends AppleConnect
{

    /**
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listProfiles() {
        $validateResult = $this->requestCore->performRequest('ios/profile/listProvisioningProfiles.action', 'POST', [
            'form_params' => [
                'pageNumber' => 1,
                'pageSize' => 500,
                'sidx' => 'sort',
                'sort' => 'name=asc',
            ],
        ]);

        $validateResult = json_decode($validateResult->getBody(), true);

        if ( $validateResult['resultCode'] != 0 ) {
            if ( $validateResult['resultCode'] != 0 ) {
                return array(
                    "resultCode" => $validateResult['resultCode'],
                    "userString" => $validateResult['userString']
                );
            }
        }

        return $validateResult;
    }

    /**
     * @param $profileId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProfileInfo($profileId) {
        $validateResult = $this->requestCore->performRequest('ios/profile/getProvisioningProfile.action', 'POST', [
            'form_params' => [
                'provisioningProfileId' => $profileId,
            ],
        ]);

        $validateResult = json_decode($validateResult->getBody(), true);

        if ( $validateResult['resultCode'] != 0 ) {
            if ( $validateResult['resultCode'] != 0 ) {
                return array(
                    "resultCode" => $validateResult['resultCode'],
                    "userString" => $validateResult['userString']
                );
            }
        }

        if ( $validateResult['resultCode'] != 0 ) {
            if ( $validateResult['resultCode'] != 0 ) {
                return array(
                    "resultCode" => $validateResult['resultCode'],
                    "userString" => $validateResult['userString']
                );
            }
        }

        if ( $validateResult['resultCode'] == 0 ) {
            return $validateResult['provisioningProfile'];
        }
    }

    /**
     * @param $profileId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadProfile($profileId) {
        $validateResult = $this->requestCore->performRequest(
            'ios/profile/downloadProfileContent',
            'GET',
            [],
            [
                'provisioningProfileId' => $profileId,
            ]);


        $accountEmail = $this->requestCore->accountEmail;
        $saved = FilesHelper::saveProfileFileToStorageWithContent($accountEmail, $profileId, $validateResult->getBody(), $this->requestCore->requestHelper->getStorage());

        if ($saved) {
            return true;
        }
        return false;

    }

    /**
     * @param $profileId
     * @param $deviceId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadProfileForDevice($profileId, $deviceId) {
        $validateResult = $this->requestCore->performRequest(
            'ios/profile/downloadProfileContent',
            'GET',
            [],
            [
                'provisioningProfileId' => $profileId,
            ]);


        $accountEmail = $this->requestCore->accountEmail;
        $saved = FilesHelper::saveProfileFileToStorageWithContentToFile($accountEmail, $profileId, $validateResult->getBody(), $deviceId, $this->requestCore->requestHelper->getStorage());

        if ($saved) {
            return true;
        }
        return false;

    }

    // type = limited = developement / store = appstore / adhoc = adhoc
    // certificate_Ids is array with certificateId
    // devices_Ids is array with udids ONLY
    /**
     * @param $name
     * @param $appId
     * @param $type
     * @param $certificateIds
     * @param $devicesIds
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createProfile($name, $appId, $type, $certificateIds, $devicesIds) {

        $validateResult = $this->requestCore->performRequest(
            'ios/profile/createProvisioningProfile.action',
            'POST',
            [
                'form_params' => [
                    'subPlatform' => '',
                    'certificateIds' => implode(',', $certificateIds),
                    'deviceIds' => implode(',', $devicesIds),
                    'template' => '',
                    'returnFullObjects' => false,
                    'distributionTypeLabel' => 'distributionTypeLabel',
                    'distributionType' => $type,
                    'appIdId' => $appId,
                    'appIdPrefix' => $this->requestCore->teamId,
                    'provisioningProfileName' => StringHelper::validateAppName($name)
                ],
            ]);

        $validateResult = json_decode($validateResult->getBody(), true);

        if ( $validateResult['resultCode'] != 0 ) {
            if ( $validateResult['resultCode'] != 0 ) {
                return array(
                    "resultCode" => $validateResult['resultCode'],
                    "userString" => $validateResult['userString']
                );
            }
        }

        if ( $validateResult['resultCode'] == 0 ) {
            return $validateResult['provisioningProfile'];
        }
    }

    /**
     * @param $profileId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteProfile($profileId) {
        $validateResult = $this->requestCore->performRequest('ios/profile/deleteProvisioningProfile.action', 'POST', [
            'form_params' => [
                'provisioningProfileId' => $profileId,
            ],
        ]);

        $validateResult = json_decode($validateResult->getBody(), true);

        if ( $validateResult['resultCode'] != 0 ) {
            if ( $validateResult['resultCode'] != 0 ) {
                return array(
                    "resultCode" => $validateResult['resultCode'],
                    "userString" => $validateResult['userString']
                );
            }
        }

        if ( $validateResult['resultCode'] == 0 ) {
            return true;
        }
    }

    /**
     * @param $profileId
     * @param $name
     * @param $appId
     * @param $type
     * @param $certificateIds
     * @param $devicesIds
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function repairProfile($profileId, $name, $appId, $type, $certificateIds, $devicesIds) {

        $validateResult = $this->requestCore->performRequest(
            'ios/profile/regenProvisioningProfile.action',
            'POST',
            [
                'form_params' => [
                    'provisioningProfileId' => $profileId,
                    'subPlatform' => '',
                    'certificateIds' => implode(',', $certificateIds),
                    'deviceIds' => implode(',', $devicesIds),
                    'template' => '',
                    'returnFullObjects' => false,
                    'distributionTypeLabel' => 'distributionTypeLabel',
                    'distributionType' => $type,
                    'appIdId' => $appId,
                    'appIdPrefix' => $this->requestCore->teamId,
                    'provisioningProfileName' => StringHelper::validateAppName($name)
                ],
            ]);


        $validateResult = json_decode($validateResult->getBody(), true);

        if ( $validateResult['resultCode'] != 0 ) {
            if ( $validateResult['resultCode'] != 0 ) {
                return array(
                    "resultCode" => $validateResult['resultCode'],
                    "userString" => $validateResult['userString']
                );
            }
        }

        if ( $validateResult['resultCode'] == 0 ) {
            return $validateResult['provisioningProfile'];
        }
    }
}