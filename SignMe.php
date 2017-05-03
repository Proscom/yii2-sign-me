<?php

namespace chumakovAnton\signMe;

use Exception;
use RuntimeException;
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

    /**
     * @var string $pathToCertificate Путь до файла сертификата
     */
    public $pathToCertificate;

    public function __construct(array $config = [])
    {
        $config = array_merge($config, require(__DIR__ . '/config.php'));
        parent::__construct($config);
    }

    public function getCertificate()
    {
        $curl = curl_init($this->urlSign);

        $options = [
            CURLOPT_CONNECT_ONLY => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CERTINFO => 1,
        ];

        curl_setopt_array($curl, $options);

        $response = curl_exec( $curl );

        $certInfo = curl_getinfo($curl, CURLINFO_CERTINFO);

        curl_close($curl);

        if (!empty($certInfo[0]['Cert']) && !empty($certInfo[0])) {
            if (file_exists($this->pathToCertificate)) {
                $fileMd5 = md5_file($this->pathToCertificate);
                $certMd5 = md5($certInfo[0]['Cert']);
                if ($fileMd5 === $certMd5) {
                    return true;
                }
            }
            $result = file_put_contents($this->pathToCertificate, $certInfo[0]['Cert']);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Подписать файл
     * @return bool
     * @throws Exception
     */
    public function sign()
    {
        if (!file_exists($this->pathToCertificate)) {
            throw new RuntimeException('File certificate:\'' . $this->pathToCertificate . '\' not found!\n
            Try execute method SignMe::getCertificate()');
        }
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

        $options = [
            CURLOPT_HTTPHEADER => ['Content-type : application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO, $this->pathToCertificate,
        ];

        curl_setopt_array($curl, $options);

        $response = curl_exec( $curl );

        curl_close($curl);

        return $this->urlSign . '/' . $response;
    }

    /**
     * Проверить подпись файла
     * @return bool
     */
    public function check()
    {
        if (!file_exists($this->pathToCertificate)) {
            throw new RuntimeException('File certificate:\'' . $this->pathToCertificate . '\' not found!\n
            Try execute method SignMe::getCertificate()');
        }
        $this->getBase64();
        $data = [
            'filet' => $this->base64,
            'md5' => $this->getMd5(),
        ];
        $data = [
            'rfile' => json_encode($data)
        ];

        $curl = curl_init($this->urlCheck);

        $options = [
            CURLOPT_HTTPHEADER => ['Content-type : application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO, $this->pathToCertificate,
        ];

        curl_setopt_array($curl, $options);

        $response = curl_exec( $curl );

        curl_close($curl);

        return $response;
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
