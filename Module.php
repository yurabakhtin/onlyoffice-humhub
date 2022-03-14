<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use humhub\modules\file\libs\FileHelper;
use humhub\libs\CURLHelper;
use \Firebase\JWT\JWT;
use yii\httpclient\Client;
use yii\httpclient\Response;

/**
 * File Module
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    public $resourcesPath = 'resources';

    /**
     * Open modes
     */
    const OPEN_MODE_VIEW = 'view';
    const OPEN_MODE_EDIT = 'edit';

    /**
     * Only document types
     */
    const DOCUMENT_TYPE_TEXT = 'word';
    const DOCUMENT_TYPE_PRESENTATION = 'slide';
    const DOCUMENT_TYPE_SPREADSHEET = 'cell';

    /**
     * @var string[] allowed spreadsheet extensions 
     */
    public $spreadsheetExtensions = ['xls', 'xlsx', 'ods', 'csv'];

    /**
     * @var string[] allowed presentation extensions 
     */
    public $presentationExtensions = ['ppsx', 'pps', 'ppt', 'pptx', 'odp'];

    /**
     * @var string[] allowed text extensions 
     */
    public $textExtensions = ['docx', 'docxf', 'oform', 'doc', 'odt', 'rtf', 'txt', 'html', 'htm', 'mht', 'pdf', 'djvu', 'fb2', 'epub', 'xps'];

    /**
     * @var string[] allowed for editing extensions
     */
    public $editableExtensions = ['xlsx', 'ppsx', 'pptx', 'docx', 'docxf', 'oform' ];
    public $convertableExtensions = ['doc','odt','xls','ods','ppt','odp','txt','csv'];

    
    public $convertsTo = [
        'doc' => 'docx',
        'odt' => 'docx',
        'txt' => 'docx',
        'xls' => 'xlsx',
        'ods' => 'xlsx',
        'csv' => 'xlsx',
        'ppt' => 'pptx',
        'odp' => 'pptx',
    ];
    

    public function isJwtEnabled() {
        return !empty($this->getJwtSecret());
    }

    public function getJwtSecret() {
        return $this->settings->get('jwtSecret');
    }

    public function getServerUrl()
    {
        return $this->settings->get('serverUrl');
    }

    public function getInternalServerUrl()
    {
        $url = $this->settings->get('internalServerUrl');

        if (empty($url)) {
            $url = $this->getServerUrl();
        }

        return $url;
    }

    public function getStorageUrl()
    {
        return $this->settings->get('storageUrl');
    }

    public function getVerifyPeerOff()
    {
        return $this->settings->get('verifyPeerOff');
    }

    /**
     * 
     * @return type
     */
    public function getServerApiUrl()
    {
        return $this->getServerUrl() . '/web-apps/apps/api/documents/api.js';
    }

    public function getDocumentType($file)
    {
        $fileExtension = strtolower(FileHelper::getExtension($file));

        if (in_array($fileExtension, $this->spreadsheetExtensions)) {
            return self::DOCUMENT_TYPE_SPREADSHEET;
        } elseif (in_array($fileExtension, $this->presentationExtensions)) {
            return self::DOCUMENT_TYPE_PRESENTATION;
        } elseif (in_array($fileExtension, $this->textExtensions)) {
            return self::DOCUMENT_TYPE_TEXT;
        }

        return null;
    }

    public function canEdit($file)
    {
        $fileExtension = strtolower(FileHelper::getExtension($file));
        return in_array($fileExtension, $this->editableExtensions);
    }

    public function canConvert($file)
    {
        $fileExtension = strtolower(FileHelper::getExtension($file));
        return in_array($fileExtension, $this->convertableExtensions);
    }

    public function canView($file)
    {
        return !empty($this->getDocumentType($file));
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to([
                    '/onlyoffice/admin'
        ]);
    }

    /**
     * Generate unique document key
     *
     * @return string
     */
    public function generateDocumentKey($file)
    {
        if (!empty($file->onlyoffice_key)) {
            return $file->onlyoffice_key;
        }

        $key = substr(strtolower(md5(Yii::$app->security->generateRandomString(20))), 0, 20);
        $file->updateAttributes(['onlyoffice_key' => $key]);
        return $key;
    }

    public function commandService($data): array
    {
        $url = $this->getInternalServerUrl() . '/coauthoring/CommandService.ashx';

        try {
            $headers = [];
            $headers['Accept'] = 'application/json';
            if ($this->isJwtEnabled()) {
                $data['token'] = JWT::encode($data, $this->getJwtSecret());
                $headers['Authorization'] = 'Bearer ' . JWT::encode(['payload' => $data], $this->getJwtSecret());
            }

            $options = array(
                'headers' => $headers,
                'body' => $data
            );

            $response = $this->request($url, 'POST', $options);
            return $response->getData();
        } catch (\Exception $ex) {
            Yii::error('Could not get document server response! ' . $ex->getMessage());
            return [];
        }
    }

    public function convertService($file, $ts): array
    {
        $url = $this->getInternalServerUrl() . '/ConvertService.ashx';
        $key = $this->generateDocumentKey($file);

        $user = Yii::$app->user->getIdentity();
        $userGuid = null;
        if (isset($user->guid)) {
            $userGuid = $user->guid;
        }

        $docHash = $this->generateHash($key, $userGuid);

        $ext = strtolower(FileHelper::getExtension($file));
        $data = [
            'async' => true,
            'embeddedfonts' => true,
            'filetype' => $ext,
            'outputtype' => $this->convertsTo[$ext],
            'key' => $key . $ts,
            'url' => Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], true),
        ];

        try {
            $headers = [];
            $headers['Accept'] = 'application/json';
            if ($this->isJwtEnabled()) {
                $data['token'] = JWT::encode($data, $this->getJwtSecret());
                $headers['Authorization'] = 'Bearer ' . JWT::encode(['payload' => $data], $this->getJwtSecret());
            }

            $options = [
                'headers' => $headers,
                'body' => $data
            ];

            $response = $this->request($url, 'POST', $options);
            return $response->getData();
        } catch (\Exception $ex) {
            $error = 'Could not get document server response! ' . $ex->getMessage();
            Yii::error($error);
            return ['error' => $error];
        }
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if (!$contentContainer) {
            return [
                new permissions\CanUseOnlyOffice(),
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function generateHash($key, $userGuid)
    {
        $data = [
            'key' => $key
        ];

        if (!empty($userGuid)) {
            $data['userGuid'] = $userGuid;
        }

        return JWT::encode($data, Yii::$app->settings->get('secret'));
    }

    /**
     * @inheritdoc
     */
    public function readHash($hash)
    {
        try {
            $data = JWT::decode($hash, Yii::$app->settings->get('secret'), array('HS256'));
        } catch (\Exception $ex) {
            $error = 'Invalid hash ' . $ex->getMessage();
            Yii::error($error);
            return [null, $error];
        }

        return [$data, null];
    }
    /**
     * Send request by URL with CURL method
     *
     * @param string $url
     * @param string $method
     * @param array $options
     * @return Response
     */
    public function request($url, $method = 'GET', $options = []): Response
    {
        $http = new Client(['transport' => 'yii\httpclient\CurlTransport']);
        $response = $http->createRequest()
            ->setUrl($url)
            ->setMethod($method)
            ->setOptions(CURLHelper::getOptions());

        if (substr($url, 0, strlen("https")) === "https" && $this->getVerifyPeerOff()) {
            $response->addOptions([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
        }

        if (array_key_exists('headers', $options)) {
            foreach ($options['headers'] as $nameHeader => $header) {
                $response->addHeaders([$nameHeader => $header]);
            }
        }

        if (array_key_exists('body', $options)) {
            $response->setContent(Json::encode($options['body']));
        }

        return $response->send();
    }

    /**
     * @var string[] mimes dictionary
     */
    public $mimes = [
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docxf' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'oform' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'epub' => 'application/epub+zip',
        'html' => 'text/html',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'otp' => 'application/vnd.oasis.opendocument.presentation-template',
        'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'ott' => 'application/vnd.oasis.opendocument.text-template',
        'pdf' => 'application/pdf',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'rtf' => 'text/rtf',
        'txt' => 'text/plain',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template'
    ];
}
