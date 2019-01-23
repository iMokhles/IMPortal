<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 23:28
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class AppsGroupsHelper extends AppleConnect
{

    /**
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listAppsGroups() {


        $validateResult = $this->requestCore->performRequest('ios/identifiers/listApplicationGroups.action', 'POST', [
            'form_params' => [
                'pageNumber' => 1,
                'pageSize' => 500,
                'sidx' => 'name',
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
     * @param $name
     * @param $bundleId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateAppGroup($name, $bundleId) {

        $validateResult = $this->requestCore->performRequest('ios/identifiers/validateApplicationGroup.action', 'POST', [
            'form_params' => [
                'name' => StringHelper::validateAppName($name),
                'identifier' => "group.".$bundleId
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
     * @param $name
     * @param $bundleId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addNewAppGroup($name, $bundleId) {

        $validateResult = $this->requestCore->performRequest('ios/identifiers/addApplicationGroup.action', 'POST', [
            'form_params' => [
                'name' => StringHelper::validateAppName($name),
                'identifier' => "group.".$bundleId
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
            return $validateResult['applicationGroup'];
        }
    }

    /**
     * @param $groupId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteAppGroup($groupId) {

        $validateResult = $this->requestCore->performRequest('ios/identifiers/deleteApplicationGroup.action', 'POST', [
            'form_params' => [
                'applicationGroup' => $groupId
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

}