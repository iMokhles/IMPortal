<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 23:32
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class CertsHelper extends AppleConnect
{

    /**
     * @param $types
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listCerts($types) {
        $validateResult = $this->requestCore->performRequest('ios/certificate/listCertRequests.action', 'POST', [
            'form_params' => [
                'pageNumber' => 1,
                'pageSize' => 500,
                'sidx' => 'sort',
                'sort' => 'name=asc&certRequestStatusCode=asc',
                'types' => implode(',', $types)
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

        return $validateResult['certRequests'];
    }

    /**
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function certDevelopmentEnabled() {
        $validateResult = $this->requestCore->performRequestForJson('cips/json/createIOSCertificate.json', 'POST', [], []);

        $validateResult = json_decode($validateResult->getBody(), true);

        $development = $validateResult['Development'];
        $developmentCert = $development[0];
        $isEnabled = $developmentCert['enabled'];

        if ($isEnabled) {
            return true;
        }
        return false;
    }

    /**
     * @param $type
     * @param $csrContent
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDevCert($type, $csrContent) {

        $validateResult = $this->requestCore->performRequest('ios/certificate/submitCertificateRequest.action', 'POST', [
            'form_params' => [
                'type' => $type,
                'csrContent' => $csrContent,
            ],
        ]);

        if ($validateResult != false) {
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

    }

    /**
     * @param $csrContent
     * @return array
     * @throws \CFPropertyList\IOException
     * @throws \CFPropertyList\PListException
     * @throws \DOMException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createXcodeDevCert($csrContent) {

        $validateResult = $this->requestCore->performOldRequest("POST", "ios/submitDevelopmentCSR.action",
            [
                'csrContent' => $csrContent,
            ], [], []);

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
     * @param $certificateId
     * @param $type
     * @return array|bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCertInfo($certificateId, $type) {
        $validateResult = $this->requestCore->performRequest('ios/certificate/getCertificate.action', 'POST', [
            'form_params' => [
                'type' => $type,
                'certificateId' => $certificateId,
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
     * @param $certificateId
     * @param $type
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function revokeCert($certificateId, $type) {
        $validateResult = $this->requestCore->performRequest('ios/certificate/revokeCertificate.action', 'POST', [
            'form_params' => [
                'type' => $type,
                'certificateId' => $certificateId,
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
        return false;
    }

    public function revokeCertByBundleId($bundleId, $type) {

        $allCerts = $this->listCerts([$type]);

        foreach ($allCerts as $cert) {

            $certName = $cert['name'];
            if ($certName === $bundleId) {
                $revoked = $this->revokeCert($cert['certificateId'], $type);
                return $revoked;
            }
        }
        return true;
    }

    /**
     * @param $certificateId
     * @param $type
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadCert($certificateId, $type) {
        $validateResult = $this->requestCore->performRequest(
            'ios/certificate/downloadCertificateContent.action',
            'GET',
            [
            ],
            [
                'type' => $type,
                'certificateId' => $certificateId,
            ]
        );

        $getCertInfo = $this->getCertInfo($certificateId, $type);
        $requesterEmail = $getCertInfo['certificate']['requesterEmail'];

        $getCertName = "";
        if ($type == CertsTypesHelper::$certDevelopment) {
            $getCertName = StringHelper::getCertNameFromCertificateContent($validateResult->getBody());
        } else if ($type == CertsTypesHelper::$certProduction) {
            $getCertName = StringHelper::getDistCertNameFromCertificateContent($validateResult->getBody());
        }

        $saved = FilesHelper::saveCertFileToStorageWithContent($requesterEmail, $validateResult->getBody(), $certificateId, $this->requestCore->requestHelper->getStorage());

        return [
            'name' => $getCertName,
            'requesterEmail' => $requesterEmail,
            'output' => $saved
        ];
    }

    /**
     * @param $certificateId
     * @param $type
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadPushCert($certificateId, $type) {
        $validateResult = $this->requestCore->performRequest(
            'ios/certificate/downloadCertificateContent.action',
            'GET',
            [
            ],
            [
                'type' => $type,
                'certificateId' => $certificateId,
            ]
        );

        $getCertInfo = $this->getCertInfo($certificateId, $type);
        $requesterEmail = $getCertInfo['certificate']['requesterEmail'];
        $getCertName = $type;

        $saved = FilesHelper::saveCertFileToStorageWithContent($requesterEmail, $validateResult->getBody(), $certificateId, $this->requestCore->requestHelper->getStorage());

        return [
            'name' => $getCertName,
            'requesterEmail' => $requesterEmail,
            'output' => $saved
        ];
    }
}