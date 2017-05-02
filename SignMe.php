<?php

namespace chumakovAnton\signMe;

use yii\base\Object;

/**
 * Class SignMe
 * @package chumakovAnton\signMe
 */
class SignMe extends Object
{
    /**
     * @var string $fullFileName Полный путь к файлу
     */
    public $fullFileName;

    /**
     * @var string $userPhone Номер телефона пользователя, подписывающего файл
     */
    public $userPhone;

    /**
     * @var string $signatureSavePath Полный путь для сохранения электронной подписи
     */
    public $signatureSavePath;

    /**
     * @var string $apiKey Ключ доступа к API
     */
    public $apiKey;

    /**
     * @var string $fileName Непосредственно имя файла с расширением
     */
    private $_fileName;

    /**
     * @var string $filePath Путь к каталогу с файлом
     */
    private $filePath;

    /**
     * @var string $fileContents Содержимое файла в строке
     */
    private $fileContents;

    /**
     * @var string $base64 Содержимое файла в кодировке base64
     */
    private $base64;

    /**
     * @var string $md5 Контрольная сумма содержимого файла
     */
    private $md5;

    /**
     * @var string $signature Цифровая подпись
     */
    private $signature;

    /**
     * @var string $urlSign Url для подписи файла
     */
    public $urlSign;

    /**
     * @var string $urlCheck Url для проверки подписи файла
     */
    public $urlCheck;

    public function __construct(array $config = [])
    {
        $config = array_merge($config, require(__DIR__ . '/config.php'));
        parent::__construct($config);
    }

    /**
     * Подписать файл
     * @return bool
     */
    public function sign()
    {
        $this->getBase64();
        $data = [
            'filet' => $this->base64,
            'fname' => $this->fileName,
            'key' => $this->apiKey,
            'user_ph' => $this->userPhone,
        ];
        $data = [
            'rfile' => json_encode($data)
        ];

        $curl = curl_init($this->urlSign);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_POST, 1);

        $response = curl_exec($curl);

        $info = curl_getinfo($curl);

        curl_close($curl);

        return $this->urlSign . '/' . $response;
    }

    /**
     * Проверить подпись файла
     * @return bool
     */
    public function check()
    {
        return true;
    }

    /**
     * Сохранить электронную подпись в файл
     */
    public function saveSignature()
    {
        return true;
    }

    public function getFileName()
    {
        if (empty($this->_fileName)) {
            $this->_fileName = basename($this->fullFileName);
        }
        return $this->_fileName;
    }

    private function getFileContents()
    {
        if (empty($this->fullFileName)) {
            return false;
        }
        if (!file_exists($this->fullFileName)) {
            return false;
        }
        $this->fileContents = file_get_contents($this->fullFileName);
        return $this->fileContents;
    }

    private function getBase64()
    {
        if (empty($this->fileContents)) {
            $this->getFileContents();
        }
        $this->base64 = base64_encode($this->fileContents);
        return $this->base64;
    }

    private function getMd5()
    {
        if (empty($this->fullFileName)) {
            return false;
        }
        $this->md5 = md5_file($this->fullFileName);
        return $this->md5;
    }
}
