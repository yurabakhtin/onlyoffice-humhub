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

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\models\ConfigureForm;
use humhub\modules\admin\components\Controller;
use yii\helpers\Url;

class AdminController extends Controller
{
    /**
     * @var Module
     */
    public $module;

    public function actionIndex()
    {
        $this->module = Yii::$app->getModule('onlyoffice');
        $model = new ConfigureForm();
        $model->loadSettings();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
        }

        $response = $this->validation();
        
        return $this->render('index', [
                                        'model' => $model, 
                                        'view' => $response
                                    ]);
    }

    private function validation()
    {
        $response['serverApiUrl'] = $this->module->getServerApiUrl();

        if(!$this->checkValidHttps()) {
            $response['error'] = "The hostname is not connected via <strong>http</strong> when using <strong>https</strong> on the hamhab server.";
            return $response;
        }

        $version = $this->getDocumentServerVersion();
        if($version === 'error 6') {
            $response['error'] = "invalid JWT token.";
            return $response;
        } elseif($version === false) {
            $response['error'] = "invalid hostname.";
            return $response;
        }

        if(!$this->getServerStatus()) {
            $response['error'] = "invalid hostname.";
            return $response;
        }

        if(!$this->checkConvertFile()) {
            $response['error'] = "invalid Server address for internal requests.";
            return $response;
        }

        $response['version'] = $version;
        return $response;
    }
    private function getDocumentServerVersion()
    {
        $response = $this->module->commandService(['c' => 'version']);
        if(!empty($response['version'])) {
            return $response['version'];
        } elseif(!empty($response['error']) && $response['error'] == 6) {
            return 'error 6';
        }
        return false;
    }

    private function getServerStatus()
    {
        $url = $this->module->getInternalServerUrl() . '/healthcheck';
        try {
            $response = $this->module->request($url);
        } catch (\Exception $ex) {
            return false;
        }
        return boolval($response->getContent());
    }
    private function checkValidHttps()
    {
        $serverUrl = $this->module->getServerUrl();
        if(empty($serverUrl))
            return false;
        $response = (isset($_SERVER['HTTPS']) && substr($serverUrl, 0, strlen("http")) === "http") ? false : true;
        return $response;
    }
    private function checkConvertFile()
    {
        $user = Yii::$app->user->getIdentity();
        $userGuid = null;
        if (isset($user->guid)) {
            $userGuid = $user->guid;
        }

        $docHash = $this->module->generateHash(null, $userGuid, true);

        $downloadUrl = Url::to(['/onlyoffice/backend/empty-file', 'doc' => $docHash], true);
        if (!empty($this->module->getStorageUrl())) {
            $downloadUrl = $this->module->getStorageUrl() . Url::to(['/onlyoffice/backend/empty-file', 'doc' => $docHash], false);
        }

        $key = substr(strtolower(md5(Yii::$app->security->generateRandomString(20))), 0, 20);

        $result = $this->module->convertService($downloadUrl, "docx", "docx", $key, false);

        $response = (!isset($result['error']) && isset($result['endConvert'])) ? $result['endConvert'] : false;

        return $response;
    }
}
