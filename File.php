<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 04.05.17
 * Time: 13:56
 */

namespace chumakovAnton\signMe;


use yii\base\Object;

/**
 * @property mixed fileName
 * @property mixed md5
 * @property mixed base64
 * @property mixed fileContents
 */
class File extends Object
{
    /**
     * @var string $fullFileName Полный путь к файлу
     */
    public $fullFileName;

    /**
     * @var string $fileName Непосредственно имя файла с расширением
     */
    private $_fileName;

    /**
     * @var string $filePath Путь к каталогу с файлом
     */
    private $_filePath;

    /**
     * @var string $_fileContents Содержимое файла в строке
     */
    private $_fileContents;

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
     * @param string $fullFileName Full path to file with filename ('/path/to/file/filename.extension')
     */
    public function __construct($fullFileName)
    {
        $this->fullFileName = $fullFileName;
    }

    /**
     * Get filename file ( '/path/to/file/filename.extension' => 'filename.extension')
     * @return string
     */
    public function getFileName()
    {
        if (empty($this->_fileName)) {
            $this->_fileName = basename($this->fullFileName);
        }
        return $this->_fileName;
    }

    /**
     * Get path to file ( '/path/to/file/filename.extension' => '/path/to/file')
     * @return string
     */
    public function getFilePath()
    {
        if (empty($this->_filePath)) {
            $pathInfo = pathinfo($this->fullFileName);
            if (empty($pathInfo)) {
                return '';
            }
            $this->_filePath = $pathInfo['dirname'];
        }
        return $this->_filePath;
    }

    /**
     * Get file contents and fill _fileContents field
     * @return bool|string
     */
    public function getFileContents()
    {
        if (empty($this->fullFileName)) {
            return false;
        }
        if (!file_exists($this->fullFileName)) {
            return false;
        }
        if (empty($this->_fileContents)) {
            $this->_fileContents = file_get_contents($this->fullFileName);
        }
        return $this->_fileContents;
    }

    /**
     * Get base64 string of file content
     * @return string
     */
    public function getBase64()
    {
        $this->_base64 = base64_encode($this->fileContents);
        return $this->_base64;
    }

    /**
     * Get md5 hash of file content
     * @return string
     */
    public function getMd5()
    {
        $this->_md5 = md5($this->fileContents);
        return $this->_md5;
    }
}