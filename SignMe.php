<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.php
 */

namespace proscom\signMe;

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
 * @package proscom\signMe
 *
 * @property resource $curlRequestOptions
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
     * @var string $urlCert Url для получения сертификатов электронной подписи
     */
    public $urlCert;

    /**
     * @var string $pathToCertificate Путь до файла ssl сертификата сервиса
     */
    public $pathToCertificate;

    /** неактивные сертификаты */
    protected const CERT_ONLY_INACTIVE = 0;
    /** активные сертификаты */
    protected const CERT_ONLY_ACTIVE = 1;
    /** сертификаты на пользователя */
    protected const CERT_ONLY_USER = 0;
    /** сертификаты на компанию */
    protected const CERT_ONLY_COMPANY = 1;
    /** формат PEM */
    protected const CERT_FORMAT_PEM = 0;
    /** формат CER */
    protected const CERT_FORMAT_CER = 1;

    /** все сертификаты */
    protected const CERT_ALL = 2;
    
    /**
     * SignMe constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = array_merge(require __DIR__ . '/config.php', $config);

        parent::__construct($config);
    }

    /**
     * SignMe init
     * @throws Exception
     */
    public function init()
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Missing required parameter "apiKey".');
        }

        if (!empty($this->pathToCertificate) && !file_exists($this->pathToCertificate)) {
            $this->initSSLCertificate();
        }

        parent::init();
    }

    /**
     * Request sign file
     * @param SignedInterface $signedEntity File for sign
     * @return string Return URL
     * @throws Exception
     */
    public function sign(SignedInterface $signedEntity): string
    {
        $data = [
            'filet' => $signedEntity->getBase64(),
            'fname' => $signedEntity->getFileName(),
            'md5' => $signedEntity->getMd5(),
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

        if (0 === stripos($response, 'error')) {
            throw new RuntimeException($response);
        }

        return $this->urlSign . '/' . $response;
    }

    /**
     * Request check signature of file
     * @param SignedInterface $signedEntity File for check
     * @return string JSON string response from sign.me
     * @throws \RuntimeException
     */
    public function check(SignedInterface $signedEntity): string
    {
        $data = [
            'md5' => $signedEntity->getMd5(),
        ];
        $data = 'rfile=' . json_encode($data);

        $ch = curl_init($this->urlCheck);

        $this->setCurlRequestOptions($ch, $data);

        $response = curl_exec($ch);

        curl_close($ch);

        if (0 === stripos($response, 'error')) {
            throw new RuntimeException($response);
        }

        return $response;
    }

    /**
     * Request for get user certificates
     * @param int|null $active - active status [SignMe::CERT_ONLY_ACTIVE, SignMe::CERT_ONLY_INACTIVE, SignMe::CERT_ALL]
     * @param int|null $allCerts - for user or company [SignMe::CERT_ONLY_USER, SignMe::CERT_ONLY_COMPANY, SignMe::CERT_ALL]
     * @param int|null $format - format of certificate [SignMe::CERT_FORMAT_CER, SignMe::CERT_FORMAT_PEM]
     * @return string
     * @throws \RuntimeException
     */
    public function requestCertificate(?int $active = self::CERT_ALL, ?int $allCerts = self::CERT_ALL, ?int $format = self::CERT_FORMAT_PEM): string
    {
        $data = [
            'get_active_certs' => $active,
            'get_all_certs' => $allCerts,
            'format' => $format,
        ];
        if (!empty($this->userPhone)) {
            $data['user_ph'] = $this->userPhone;
        }
        if (!empty($this->companyOGRN)) {
            $data['company_ogrn'] = $this->companyOGRN;
        }

        $ch = curl_init($this->urlCert);

        $this->setCurlRequestOptions($ch, $data);

        $response = curl_exec($ch);

        curl_close($ch);

        if (0 === stripos($response, 'error')) {
            throw new RuntimeException($response);
        }

        return $response;
    }

    /**
     * Request for get SSL certificate from sign.me
     * @return bool
     * @throws \yii\base\Exception
     */
    private function initSSLCertificate(): bool
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
    private function setCurlRequestOptions($ch, $postData): bool
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
