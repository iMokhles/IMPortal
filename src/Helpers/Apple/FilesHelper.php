<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 23:33
 */

namespace iMokhles\IMPortal\Helpers\Apple;


class FilesHelper
{

    /**
     * @param $accountEmail
     * @param $content
     * @param $filename
     * @param \IMStorage $storage
     * @return bool
     */
    public static function saveCertFileToStorageWithContent($accountEmail, $content, $filename, $storage)
    {
        $newFilePath = "certificates/".$accountEmail."/".$filename.".cer";

        if ($storage->has($newFilePath)) {
            $storage->delete($newFilePath);
        }
        if (!$storage->has($newFilePath))
        {
            $savedCert =  $storage->putFileAs($content, $newFilePath);

            return $savedCert;
        }
        return false;
    }

    /**
     * @param $accountEmail
     * @param $profileId
     * @param $content
     * @param \IMStorage $storage
     * @return bool
     */
    public static function saveProfileFileToStorageWithContent($accountEmail, $profileId, $content, $storage)
    {
        $newFilePath = "profiles/".$accountEmail."/".$profileId.".mobileprovision";

        if ($storage->has($newFilePath)) {
            $storage->delete($newFilePath);
        }
        if (!$storage->has($newFilePath))
        {
            $savedCert =  $storage->putFileAs($content, $newFilePath);

            return $savedCert;
        }
        return false;
    }

    /**
     * @param $accountEmail
     * @param $profileId
     * @param $content
     * @param $deviceId
     * @param \IMStorage $storage
     * @return bool
     */
    public static function saveProfileFileToStorageWithContentToFile($accountEmail, $profileId, $content, $deviceId, $storage)
    {
        $newFilePath = "profiles/".$accountEmail."/".$deviceId."/".$profileId.".mobileprovision";

        if ($storage->has($newFilePath)) {
            $storage->delete($newFilePath);
        }
        if (!$storage->has($newFilePath))
        {
            $savedCert =  $storage->putFileAs($content, $newFilePath);

            return $savedCert;
        }
        return false;
    }
}