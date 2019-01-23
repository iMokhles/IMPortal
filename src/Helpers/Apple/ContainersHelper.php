<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 23:37
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class ContainersHelper extends AppleConnect
{
    /**
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listCloudContainers() {
        $validateResult = $this->requestCore->performRequest('cloudContainer/listCloudContainers.action', 'POST', [
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
     * @param $name
     * @param $bundleId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateCloudContainer($name, $bundleId) {
        $validateResult = $this->requestCore->performRequest('cloudContainer/validateCloudContainer.action', 'POST', [
            'form_params' => [
                'name' => StringHelper::validateAppName($name),
                'identifier' => "iCloud.".$bundleId,
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
    public function createCloudContainer($name, $bundleId) {
        $validateResult = $this->requestCore->performRequest('cloudContainer/addCloudContainer.action', 'POST', [
            'form_params' => [
                'name' => StringHelper::validateAppName($name),
                'identifier' => "iCloud.".$bundleId,
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
            return $validateResult['cloudContainer'];
        }
    }
}