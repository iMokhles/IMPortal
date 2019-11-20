<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 06/10/2017
 * Time: 18:18
 */

namespace iMokhles\IMPortal\Helpers\Apple;


use iMokhles\IMPortal\Storage\IMStorage;

class RequestHelper
{

    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var IMStorage
     */
    private $storage;

    public function __construct($storagePath)
    {
        $this->storagePath = $storagePath;
        $this->storage = new IMStorage($this->storagePath);
    }

    public function getServiceKey() {

        $url = "https://olympus.itunes.apple.com/v1/app/config?hostname=itunesconnect.apple.com";
        $ch = curl_init();
        // Set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Xcode',
            'Content-Type: application/json',
            'Accept-Language: en-us',
            'Connection: close'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        // Execute and close cURL
        $data = curl_exec($ch);
        curl_close($ch);

        $dataArray = json_decode($data, true);
        return $dataArray['authServiceKey'];
    }

    public function generateCSR($name, $email, $password) {
        $dn = array(
            "countryName" => "FR",
            "stateOrProvinceName" => "n-n",
            "localityName" => "n-n",
            "organizationName" => $name,
            "organizationalUnitName" => $name." Team",
            "commonName" => $name,
            "emailAddress" => $email
        );

        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha256'));

        // Generate a self-signed cert, valid for 365 days
        $x509 = openssl_csr_sign($csr, null, $privkey, $days=1460, array('digest_alg' => 'sha256'));

        // Save your private key, CSR and self-signed cert for later use

        openssl_csr_export_to_file($csr, $this->storage->getStoragePath('csr/CertificateSigningRequest.csr'));
        openssl_x509_export_to_file($x509, $this->storage->getStoragePath('csr/CertificateSigningRequestSigned.pem'));
        openssl_pkey_export_to_file($privkey, $this->storage->getStoragePath('csr/CertificateSigningRequestKey.key'), $password);
    }

    public function generatePushP12File($account_email, $cerFile, $type, $keyFile, $password) {


        $this->storage->makeDirectory('push_certs/'.$account_email.'/'.$type);

        $outPutPemFile = $this->storage->getStoragePath('push_certs/'.$account_email.'/'.$type.'/apn_cert.pem');
        $outPutP12File = $this->storage->getStoragePath('push_certs/'.$account_email.'/'.$type.'/apn_cert_key.p12');

        shell_exec('openssl x509 -in '.$this->storage->getStoragePath($cerFile).' -inform DER -out '.$outPutPemFile.' -outform PEM');
        shell_exec('openssl pkcs12 -export -inkey '.$keyFile.' -in '.$outPutPemFile.' -out '.$outPutP12File.' -passin pass:'.$password.' -passout pass:'.$password);


        return [
            'apn_cert' => $outPutPemFile,
            'apn_cert_key' => $outPutP12File
        ];
    }

    /**
     * @return IMStorage
     */
    public function getStorage() {
        return $this->storage;
    }
}