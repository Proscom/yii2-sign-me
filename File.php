<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 04.05.17
 * Time: 13:56
 */

namespace proscom\signMe;


use yii\base\BaseObject;

/**
 * @property string fileName
 * @property string md5
 * @property string base64
 * @property string $content
 * @property string $fileContents
 * @property string _content
 */
class File extends BaseObject implements SignedInterface
{
    /**
     * @var string $fileName Непосредственно имя файла с расширением
     */
    private $_fileName;

    /**
     * @var string $_content Содержимое файла в строке
     */
    private $_content;

    /**
     * @var string $_base64 Содержимое файла в кодировке _base64
     */
    private $_base64;

    /**
     * @var string $_md5 Контрольная сумма содержимого файла
     */
    private $_md5;

    /**
     * File constructor.
     * @param string $name Имя файла
     * @param string $content Содержимое файла
     * @param array $config
     */
    public function __construct(string $name, string $content, array $config = [])
    {
        parent::__construct($config);

        $this->setFileName($name);
        $this->setContent($content);
    }

    /**
     * Get filename file
     * @return string
     */
    public function getFileName(): string
    {
        return $this->_fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * Get content
     * @return string
     */
    public function getFileContents(): string
    {
        return $this->_content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->_content = $content;
    }

    /**
     * Get base64 string of file content
     * @return string
     */
    public function getBase64(): string
    {
        $this->_base64 = base64_encode($this->_content);
        return $this->_base64;
    }

    /**
     * Get md5 hash of file content
     * @return string
     */
    public function getMd5(): string
    {
        $this->_md5 = md5($this->_content);
        return $this->_md5;
    }
}