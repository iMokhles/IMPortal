<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 22:22
 */

namespace iMokhles\IMPortal\Storage;

class IMStorage
{

    /**
     * @var string
     */
    private $storagePath;

    /**
     * IMStorage constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->storagePath = $path;
    }

    /**
     * @param $path
     * @return bool
     */
    public function delete($path) {
        $fullPath = $this->storagePath.DIRECTORY_SEPARATOR.$path;

        if (is_dir($fullPath)) {
            return rmdir($fullPath);
        } else {
            return unlink($fullPath);
        }
    }

    /**
     * @param $path
     * @return bool
     */
    public function has($path) {
        return file_exists($this->storagePath.DIRECTORY_SEPARATOR.$path);
    }

    /**
     * @param $data
     * @param $outputDir
     * @return bool|int
     */
    public function putFileAs($data, $outputDir) {
        return file_put_contents($this->storagePath.DIRECTORY_SEPARATOR.$outputDir, $data);
    }

    /**
     * @param $path
     * @return string
     */
    public function getStoragePath($path) {

        return $this->storagePath.DIRECTORY_SEPARATOR.$path;
    }

    /**
     * @param $path
     * @return bool
     */
    public function makeDirectory($path) {
        return mkdir( $this->storagePath.DIRECTORY_SEPARATOR.$path, 0777, true );
    }
}