<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 06/10/2017
 * Time: 18:34
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class AppsHelper extends AppleConnect
{

    /**
     * @param $name
     * @param $bundleId
     * @param $type
     * @param $typeBundleId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateApp($name, $bundleId, $type, $typeBundleId) {

        $validateOptions = null;

        if ( $type == 1 ) {
            if( strpos( $bundleId, "*" ) !== false ) {
                $validateOptions = [
                    'appIdName' => $name,
                    'appIdentifierString' => $bundleId,
                    'type' => 'wildcard',
                    'wildcardIdentifier' => $typeBundleId,
                    'dataProtectionPermission' => 'on',
                    'dataProtectionPermissionLevel' => 'complete',
                    'iCloud' => true,
                    'cloudKitVersion' => 1
                ];
            } else {
                return array(
                    "resultCode" => 56789,
                    "userString" => "Enter Valid Bundle Id for Wildcard App"
                );
            }

        } else if ( $type == 2 ) {
            $validateOptions = [
                'appIdName' => $name,
                'appIdentifierString' => $bundleId,
                'type' => 'explicit',
                'explicitIdentifier' => $typeBundleId,
                'dataProtectionPermission' => 'on',
                'dataProtectionPermissionLevel' => 'complete',
                'push' => 'on',
            ];
        } else {
            return ['message' => 'something_went_wrong'];
        }

        $validateResult = $this->requestCore->performRequest('ios/identifiers/validateAppId.action', 'POST', [
            'form_params' => $validateOptions,
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
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listApps() {


        $validateResult = $this->requestCore->performRequest('ios/identifiers/listAppIds.action', 'POST', [
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
     * @param $appIdId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAppInfo($appIdId) {

        $validateResult = $this->requestCore->performRequest('ios/identifiers/getAppIdDetail.action', 'POST', [
            'form_params' => [
                'appIdId' => $appIdId,
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

        return $validateResult['appId'];
    }

    /**
     * @param $name
     * @param $bundle_id
     * @param int $type
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addNewAppId($name, $bundle_id, $type = 1) {

        $formOptions = null;

        if ( $type == 1 ) {
            if( strpos( $bundle_id, "*" ) !== false ) {
                $queryOptions['type'] = 'wildcard';
                $queryOptions['identifier'] = $bundle_id;

                $formOptions = [
                    'name' => StringHelper::validateAppName($name),
                    'identifier' => $bundle_id,
                    'prefix' => $this->requestCore->teamId,
                    'type' => $type,
                    'cloudKitVersion' => 1,
                    'dataProtectionPermissionLevel' => 'complete',
                    'iCloud' => true,
                    'dataProtectionPermission' => true,
                ];

            } else {
                return false;
            }
        } else if ( $type == 2 ) {

            $formOptions = [
                'name' => StringHelper::validateAppName($name),
                'identifier' => $bundle_id,
                'prefix' => $this->requestCore->teamId,
                'type' => 'explicit',
                'cloudKitVersion' => 1,
                'dataProtectionPermissionLevel' => 'complete',
                'dataProtectionPermission' => true,
                'iCloud' => true,
                'push' => true,
                'MP49FN762P' => true,
            ];

        } else if ( $type == 3 ) {

            $formOptions = [
                'name' => StringHelper::validateAppName($name),
                'identifier' => $bundle_id,
                'prefix' => $this->requestCore->teamId,
                'type' => 'explicit',
                'cloudKitVersion' => 1,
                'dataProtectionPermissionLevel' => 'complete',
                'dataProtectionPermission' => true,
                'iCloud' => true,
                'MP49FN762P' => true,
            ];

        } else {
            return false;
        }

        $validateResult = $this->requestCore->performRequest('ios/identifiers/addAppId.action', 'POST', [

            'form_params' => $formOptions,
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

        return $validateResult['appId'];
    }

    /**
     * @param $name
     * @param $bundle_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addNewSpecificDevAppId($name, $bundle_id) {

        $formOptions = null;

        $formOptions = [
            'name' => StringHelper::validateAppName($name),
            'identifier' => $bundle_id,
            'prefix' => $this->requestCore->teamId,
            'type' => 'explicit',
            'cloudKitVersion' => 2,
            'dataProtectionPermissionLevel' => 'complete',
            'dataProtectionPermission' => true,
            'iCloud' => true,
            'push' => true,
            'APG3427HIY' => true,
            'SI015DKUHP' => true,
            'IAD53UNK2F' => true,
            'MP49FN762P' => true,
        ];

        $validateResult = $this->requestCore->performRequest('ios/identifiers/addAppId.action', 'POST', [

            'form_params' => $formOptions,
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

        $appIdId = $validateResult['appId']['appIdId'];

        return $appIdId;

    }

    /**
     * @param $appIdId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteApp($appIdId) {

        $validateResult = $this->requestCore->performRequest('ios/identifiers/deleteAppId.action', 'POST', [
            'form_params' => [
                'appIdId' => $appIdId,
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
     * @param $appIdId
     * @param $service
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateAppService($appIdId, $service) {

        $serviceUri = null;
        if ($service['key'] === "push") {
            $serviceUri = "account/ios/identifiers/updatePushService.action";
        } else {
            $serviceUri = "account/ios/identifiers/updateService.action";
        }
        $validateResult = $this->requestCore->performRequest('/services-account/QH65B2/'.$serviceUri, 'POST', [], [
            'appIdId' => $appIdId,
            'displayId' => $appIdId,
            'featureType' => $service['key'],
            'featureValue' => $service['value'],
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
     * @param $appIdId
     * @param $groups
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function associateGroupsWithApp($appIdId, $groups) {
        $validateResult = $this->requestCore->performRequest('ios/identifiers/assignApplicationGroupToAppId.action', 'POST', [
            'form_params' => [
                'appIdId' => $appIdId,
                'displayId' => $appIdId,
                'applicationGroups' => implode(',', $groups),
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
     * @param $appIdId
     * @param $newAppName
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateAppName($appIdId, $newAppName) {
        $validateResult = $this->requestCore->performRequest('ios/identifiers/updateAppIdName.action', 'POST', [
            'form_params' => [
                'appIdId' => $appIdId,
                'name' => StringHelper::validateAppName($newAppName),
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


    // type 1 = Push Development
    // type 2 = Push Productions
    /**
     * @param $appIdId
     * @param int $type
     * @param $csrContent
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function associatePushCertificateWithApp($appIdId, $type = 1, $csrContent) {

        $pushOptions = [
            'csrContent' => $csrContent,
            'appIdId' => $appIdId,
            'specialIdentifierDisplayId' => $appIdId
        ];

        if ($type == 1) {
            // Development
            $pushOptions['type'] = CertsTypesHelper::$certDevelopmentPush;

        } else if ($type == 2) {
            // Production
            $pushOptions['type'] = CertsTypesHelper::$certProductionPush;
        }
        $validateResult = $this->requestCore->performRequest('ios/certificate/submitCertificateRequest.action', 'POST', [
            'form_params' => $pushOptions,
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
        return $validateResult['certRequest'];
    }

    /**
     * @param $appIdId
     * @param $cloudContainers
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function associateCloudContainersWithApp($appIdId, $cloudContainers) {
        $validateResult = $this->requestCore->performRequest('ios/identifiers/assignCloudContainerToAppId.action', 'POST', [
            'form_params' => [
                'appIdId' => $appIdId,
                'cloudContainers' => implode(',', $cloudContainers),
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