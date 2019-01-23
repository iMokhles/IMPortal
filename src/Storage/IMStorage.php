<?php
/**
 * Created by PhpStorm.
 * User: imokhles
 * Date: 23/01/2019
 * Time: 22:22
 */

class IMStorage
{

    /**
     * @var string
     */
    private $storagePath;

    public function __construct($path)
    {
        $this->storagePath = $path;
    }

    public function putFileAs($data, $outputDir) {
        return file_put_contents($this->storagePath.DIRECTORY_SEPARATOR.$outputDir, $data);
    }

    public function getStoragePath($path) {

        return $this->storagePath.DIRECTORY_SEPARATOR.$path;
    }

    public function makeDirectory($path) {
        return mkdir( $this->storagePath.DIRECTORY_SEPARATOR.$path, 0777, true );
    }
}