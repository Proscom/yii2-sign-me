<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.php
 */

namespace chumakovAnton\signMe;

use Exception;
use RuntimeException;
use yii\base\Object;
use yii\helpers\FileHelper;

/**
 * Class SignMe
 *
 * Sign.me service API library
 *
 * @author Chumakov Anton <anton.4umakov@yandex.ru>
 *
 * @package chumakovAnton\signMe
 */
class SignMe extends Object
{
    /**
     * @var string $userPhone Номер телефона пользователя, подписывающего файл
     */
    public $userPhone;

    /**
     * @var string $userEmail Email пользователя, подписывающего файл
     */
    public $userEmail;

    /**
     * @var string $companyINN ИНН компании, подписывающей файл
     */
    public $companyINN;

    /**
     * @var string $companyOGRN ОГРН компании, подписывающей файл
     */
    public $companyOGRN;

    /**
     * @var integer $noEmail Установить 1, если нужно не высылать пользователю емеил
     */
    public $noEmail;

    /**
     * @var integer $forceSms Установить 1, если необходима двухфакторная авторизация
     */
    public $forceSms;

    /**
     * @var string $apiKey Ключ доступа к API
     */
    public $apiKey;

    /**
     * @var string $returnUrl Адрес возврата после подписания
     */
    public $returnUrl;

    /**
     * @var string $urlSign Url для подписи файла
     */
    public $urlSign;

    /**
     * @var string $urlCheck Url для проверки подписи файла
     */
    public $urlCheck;

    /**
     * @var string $pathToCertificate Путь до файла ssl сертификата сервиса
     */
    public $pathToCertificate;

    /**
     * SignMe constructor.
     * @param string $apiKey
     * @param array $config
     * @throws \yii\base\Exception
     */
    public function __construct($apiKey, array $config = [])
    {
        $config = array_merge(require __DIR__ . '/config.php', $config);

        parent::__construct($config);

        $this->apiKey = $apiKey;

        if (!empty($this->pathToCertificate) && !file_exists($this->pathToCertificate)) {
            $this->getCertificate();
        }
    }

    /**
     * Request sign file
     * @param string $fullFileName Full path to file with filename ('/path/to/file/filename.extension')
     * @return string Return URL
     * @throws Exception
     */
    public function sign($fullFileName)
    {
        $file = new File($fullFileName);

        $data = [
            'filet' => $file->base64,
            'fname' => $file->fileName,
            'md5' => $file->md5,
            'key' => $this->apiKey,
        ];
        if (!empty($this->returnUrl)) {
            $data['url'] = $this->returnUrl;
        }
        if (!empty($this->userEmail)) {
            $data['user_email'] = $this->userEmail;
        }
        if (!empty($this->userPhone)) {
            $data['user_ph'] = $this->userPhone;
        }
        if (!empty($this->companyINN)) {
            $data['company_inn'] = $this->companyINN;
        }
        if (!empty($this->companyOGRN)) {
            $data['company_ogrn'] = $this->companyOGRN;
        }
        if (!empty($this->noEmail)) {
            $data['noemail'] = 1;
        }
        if (!empty($this->forceSms)) {
            $data['forcesms'] = 1;
        }

        $data = 'rfile=' . json_encode($data);

        $ch = curl_init($this->urlSign);

        $this->setCurlRequestOptions($ch, $data);

        $response = curl_exec($ch);

        curl_close($ch);

        if (0===stripos($response, 'error')) {
            throw new RuntimeException($response);
        }

        return $this->urlSign . '/' . $response;
    }

    /**
     * Request check signature of file
     * @param string $fullFileName Full path to file with filename ('/path/to/file/filename.extension')
     * @return string JSON string response from sign.me
     * @throws Exception
     */
    public function check($fullFileName)
    {
        $file = new File($fullFileName);

        $data = [
            //'filet' => $file->base64,
            'md5' => $file->md5,
        ];
        $data = 'rfile=' . json_encode($data);

        $ch = curl_init($this->urlCheck);

        $this->setCurlRequestOptions($ch, $data);

        $response = curl_exec($ch);

        curl_close($ch);

        if (0===stripos($response, 'error')) {
            throw new RuntimeException($response);
        }

        return $response;
    }

    /**
     * Request for get SSL certificate from sign.me
     * @return bool
     * @throws \yii\base\Exception
     */
    private function getCertificate()
    {
        $ch = curl_init($this->urlSign);

        $options = [
            CURLOPT_CONNECT_ONLY => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CERTINFO => 1,
        ];

        curl_setopt_array($ch, $options);

        curl_exec($ch);

        $certInfo = curl_getinfo($ch, CURLINFO_CERTINFO);

        curl_close($ch);

        if (!empty($certInfo[0]) && !empty($certInfo[0]['Cert'])) {
            if (file_exists($this->pathToCertificate)) {
                $fileMd5 = md5_file($this->pathToCertificate);
                $certMd5 = md5($certInfo[0]['Cert']);
                if ($fileMd5 === $certMd5) {
                    return true;
                }
            }

            $pathInfo = pathinfo($this->pathToCertificate);
            if (empty($pathInfo)) {
                return false;
            }
            $filePath = $pathInfo['dirname'];

            FileHelper::createDirectory($filePath);

            $result = file_put_contents($this->pathToCertificate, $certInfo[0]['Cert']);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param resource $ch
     * @param mixed $postData Post fields content
     * @return bool
     */
    private function setCurlRequestOptions($ch, $postData)
    {
        $options = [
            CURLOPT_HTTPHEADER => ['Content-type : application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
        ];

        $sslOptions = [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO, $this->pathToCertificate,
        ];

        if (empty($this->pathToCertificate)) {
            $sslOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ];
        }

        $options += $sslOptions;

        return curl_setopt_array($ch, $options);
    }
}
