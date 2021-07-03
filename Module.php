<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlyoffice;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use humhub\modules\file\libs\FileHelper;
use humhub\libs\CURLHelper;
use \Firebase\JWT\JWT;

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
    const DOCUMENT_TYPE_TEXT = 'text';
    const DOCUMENT_TYPE_PRESENTATION = 'presentation';
    const DOCUMENT_TYPE_SPREADSHEET = 'spreadsheet';

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
    public $textExtensions = ['docx', 'doc', 'odt', 'rtf', 'txt', 'html', 'htm', 'mht', 'pdf', 'djvu', 'fb2', 'epub', 'xps'];

    /**
     * @var string[] allowed for editing extensions
     */
    public $editableExtensions = ['xlsx', 'ppsx', 'pptx', 'docx' ];
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
        $fileExtension = FileHelper::getExtension($file);

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
        $fileExtension = FileHelper::getExtension($file);
        return in_array($fileExtension, $this->editableExtensions);
    }

    public function canConvert($file)
    {
        $fileExtension = FileHelper::getExtension($file);
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

    public function commandService($data)
    {
        $url = $this->getInternalServerUrl() . '/coauthoring/CommandService.ashx';

        try {
            $http = new \Zend\Http\Client($url, [
                'adapter' => '\Zend\Http\Client\Adapter\Curl',
                'curloptions' => CURLHelper::getOptions(),
                'timeout' => 10
            ]);
            $http->setMethod('POST');
            $headers = $http->getRequest()->getHeaders();

            if ($this->isJwtEnabled()) {
                $data['token'] = JWT::encode($data, $this->getJwtSecret());
                $headers->addHeaderLine('Authorization', 'Bearer ' . JWT::encode(['payload' => $data], $this->getJwtSecret()));
            }

            $http->setRawBody(Json::encode($data));
            $headers->addHeaderLine('Accept', 'application/json');

            $response = $http->send();
            $json = $response->getBody();
        } catch (\Exception $ex) {
            Yii::error('Could not get document server response! ' . $ex->getMessage());
            return [];
        }

        try {
            return Json::decode($json);
        } catch (\yii\base\InvalidParamException $ex) {
            Yii::error('Could not get document server response! ' . $ex->getMessage());
            return [];
        }
    }

    public function convertService($file, $ts)
    {
        $url = $this->getInternalServerUrl() . '/ConvertService.ashx';
        $key = $this->generateDocumentKey($file);

        $ext = FileHelper::getExtension($file);
        $data = [
            'async' => true,
            'embeddedfonts' => true,
            'filetype' => $ext,
            'outputtype' => $this->convertsTo[$ext],
            'key' => $key . $ts,
            'url' => Url::to(['/onlyoffice/backend/download', 'key' => $key], true),
        ];

        try {
            $http = new \Zend\Http\Client($url, [
                'adapter' => '\Zend\Http\Client\Adapter\Curl',
                'curloptions' => CURLHelper::getOptions(),
                'timeout' => 10
            ]);
            $http->setMethod('POST');
            $headers = $http->getRequest()->getHeaders();

            if ($this->isJwtEnabled()) {
                $data['token'] = JWT::encode($data, $this->getJwtSecret());
                $headers->addHeaderLine('Authorization', 'Bearer ' . JWT::encode(['payload' => $data], $this->getJwtSecret()));
            }

            $http->setRawBody(Json::encode($data));
            $headers->addHeaderLine('Accept', 'application/json');

            $response = $http->send();
            $json = $response->getBody();
        } catch (\Exception $ex) {
            $error = 'Could not get document server response! ' . $ex->getMessage();
            Yii::error($error);
            return [ 'error' => $error ];
        }

        try {
            return Json::decode($json);
        } catch (\yii\base\InvalidParamException $ex) {
            $error = 'Could not get document server response! ' . $ex->getMessage();
            Yii::error($error);
            return [ 'error' => $error ];
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

}
