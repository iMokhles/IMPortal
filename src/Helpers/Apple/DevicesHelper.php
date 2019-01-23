<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 23:38
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class DevicesHelper extends AppleConnect
{

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listDevices() {
        $validateResult = $this->requestCore->performRequest('ios/device/listDevices.action', 'POST', [
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

        return $validateResult['devices'];
    }

    /**
     * @param $deviceClasses
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listDevicesByClass($deviceClasses) {
        $validateResult = $this->requestCore->performRequest('ios/device/listDevices.action', 'POST', [
            'form_params' => [
                'pageNumber' => 1,
                'pageSize' => 500,
                'sidx' => 'name',
                'sort' => 'name=asc',
                'deviceClasses' => implode(',', $deviceClasses)
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

        return $validateResult['devices'];
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listDevicesCounts() {
        $devices = $this->listDevices();

        if (isset($devices['resultCode'])) {
            return [
                'iphone' => 0,
                'ipads' => 0,
                'ipods' => 0,
                'tvs' => 0,
                'watchs' => 0,
            ];
        }
        $iphone_Devices = [];
        $ipad_Devices = [];
        $ipod_Devices = [];
        $tv_Devices = [];
        $watch_Devices = [];

        foreach ($devices as $item) {
            if (StringHelper::constains($item['deviceClass'], "iphone")) {
                array_push($iphone_Devices, $item);
            } else if (StringHelper::constains($item['deviceClass'], "ipad")) {
                array_push($ipad_Devices, $item);
            } else if (StringHelper::constains($item['deviceClass'], "ipod")) {
                array_push($ipod_Devices, $item);
            } else if (StringHelper::constains($item['deviceClass'], "tv")) {
                array_push($tv_Devices, $item);
            } else if (StringHelper::constains($item['deviceClass'], "watch")) {
                array_push($watch_Devices, $item);
            }
        }

        return [
            'iphone' => array('count' => 100-count($iphone_Devices)),
            'ipads' => array('count' => 100-count($ipad_Devices)),
            'ipods' => array('count' => 100-count($ipod_Devices)),
            'tvs' => array('count' => 100-count($tv_Devices)),
            'watchs' => array('count' => 100-count($watch_Devices)),
        ];

    }

    /**
     * @param $udid
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDeviceInfo($udid) {
        $devices = $this->listDevices();

        if (array_key_exists('userString', $devices)) {
            return [];
        }
        foreach ($devices as $item) {
            if (strtolower($item['deviceNumber']) === strtolower($udid)) {
                return $item;
            }
        }
        return [];
    }

    /**
     * @param $udid
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDeviceInfo2($udid) {
        $devices = $this->listDevices();

        if (array_key_exists('userString', $devices)) {
            return [];
        }
        foreach ($devices as $item) {

            if (strtolower($item['deviceNumber']) === strtolower($udid)) {
                return $item;
            }
        }
        return [];
    }

    /**
     * @param $udid
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isDeviceAlreadyAdded($udid) {
        $devices = $this->listDevices();

        if (array_key_exists('userString', $devices)) {
            return false;
        }
        foreach ($devices as $item) {

            if (strtolower($item['deviceNumber']) === strtolower($udid)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function allDevicesIds() {
        $devices = $this->listDevices();

        $devicesArray = [];
        foreach ($devices as $item) {
            array_push($devicesArray, $item['deviceId']);
        }
        return $devicesArray;
    }

    /**
     * @param $device_number
     * @param $name
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDeviceClassBeforeRegister($device_number, $name) {
        $device = $this->validateDevice($device_number, $name);

        if (array_key_exists('deviceClass', $device)) {
            $deviceClass = $device['deviceClass'];
            return $deviceClass;
        } else {
            return null;
        }
    }

    /**
     * @param $device_number
     * @param $name
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateDevice($device_number, $name) {
        $validateResult = $this->requestCore->performRequest('ios/device/validateDevices.action', 'POST', [
            'form_params' => [
                'deviceClasses' => '',
                'register' => 'single',
                'deviceNames' => StringHelper::validateAppName($name),
                'deviceNumbers' => $device_number,
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

        $devices = $validateResult["devices"];
        if (count($devices) > 0) {
            $device = $devices[0];
            return $device;
        } else {
            $validationMessages = $validateResult["validationMessages"];
            $message = $validationMessages[0];
            return ['message' => $message["validationUserMessage"]];
        }
    }

    /**
     * @param $device_number
     * @param $name
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addDevice($device_number, $name) {

        $validateResult = $this->requestCore->performRequest('ios/device/addDevices.action', 'POST', [
            'form_params' => [
                'deviceClasses' => '',
                'register' => 'single',
                'deviceNames' => StringHelper::validateAppName($name),
                'deviceNumbers' => $device_number,
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
     * @param $deviceId
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function disableDevice($deviceId) {
        $validateResult = $this->requestCore->performRequest('ios/device/deleteDevice.action', 'POST', [
            'form_params' => [
                'deviceId' => $deviceId
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
     * @param $deviceId
     * @param $device_number
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function enableDevice($deviceId, $device_number) {
        $validateResult = $this->requestCore->performRequest('ios/device/enableDevice.action', 'POST', [
            'form_params' => [
                'displayId' => $deviceId,
                'deviceNumber' => $device_number,
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