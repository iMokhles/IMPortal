<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 08/10/2017
 * Time: 00:59
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class AppServicesHelper
{

    static $AppGroupServiceID = "APG3427HIY"; // true/false
    static $ApplePayServiceID = "OM633U5T5G"; // true/false
    static $AssociatedDomainsServiceID = "SKC3T5S89Y"; // true/false


    static $DataProtectionServiceID = "dataProtection"; // complete/unlessopen/untilfirstauth
    static $DataProtectionServiceCompleteValue = "complete";
    static $DataProtectionServiceUnlessOpenValue = "unlessopen";
    static $DataProtectionServiceUntilFirstAuthValue = "untilfirstauth";


    static $GameCenterServiceID = "gameCenter"; // true/false
    static $HealthKitServiceID = "HK421J6T7P"; // true/false
    static $HomeKitServiceID = "homeKit"; // true/false
    static $WirelessAccessoryServiceID = "WC421J6T7P"; // true/false
    static $CloudServiceID = "iCloud"; // true/false
    static $CloudKitServiceID = "cloudKitVersion"; // 1/2
    static $InAppPurchaseServiceID = "inAppPurchase"; // true/false
    static $InterAppAudioServiceID = "IAD53UNK2F"; // true/false
    static $PassbookServiceID = "pass"; // true/false
    static $PushNotificationServiceID = "push"; // true/false
    static $SiriKitServiceID = "SI015DKUHP"; // true/false
    static $VPNConfigurationServiceID = "V66P55NK2I"; // true/false
    static $NetworkExtensionServiceID = "NWEXT04537"; // true/false
    static $HotspotServiceID = "HSC639VEI8"; // true/false
    static $MultipathServiceID = "MP49FN762P"; // true/false
    static $NFCTagReadingServiceID = "NFCTRMAY17"; // true/false

}