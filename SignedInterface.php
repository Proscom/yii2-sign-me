<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 06.09.17
 * Time: 19:52
 */

namespace proscom\signMe;


interface SignedInterface
{

    /**
     * Get name of signed data
     * @return string
     */
    public function getFileName(): string;

    /**
     * Get signed content
     * @return string
     */
    public function getFileContents(): string;

    /**
     * Get base64 string of signed content
     * @return string
     */
    public function getBase64(): string;

    /**
     * Get md5 hash of signed content
     * @return string
     */
    public function getMd5(): string;
}