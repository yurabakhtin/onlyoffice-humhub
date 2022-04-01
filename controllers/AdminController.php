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

class AdminController extends Controller
{

    public function actionIndex()
    {
        $module = Yii::$app->getModule('onlyoffice');
        $serverApiUrl = "";
        $model = new ConfigureForm();
        $model->loadSettings();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            $serverApiUrl = Yii::$app->getModule('onlyoffice')->getServerApiUrl();
        }

        $invalidHttps = $this->checkValidHttps($model->serverUrl);
        $serverStatus = $this->getServerStatus($module);
        $response = $this->getDocumentServerVersion($module);
        $conversion = $this->checkConvertFile($module);
        
        return $this->render('index', [
                                        'model' => $model, 
                                        'view' => $response, 
                                        'serverApiUrl' => $serverApiUrl, 
                                        'serverStatus' => $serverStatus, 
                                        'invalidHttps' => $invalidHttps, 
                                        'conversion' => $conversion
                                    ]);
    }

    private function getDocumentServerVersion($module)
    {
        return $module->commandService(['c' => 'version']);
    }

    private function getServerStatus($module)
    {
        $url = $module->getInternalServerUrl() . '/healthcheck';
        try {
            $response = $module->request($url);
        } catch (\Exception $ex) {
            Yii::error('Internal server url error' . $ex->getMessage());
            return false;
        }
        return boolval($response->getBody());
    }
    private function checkValidHttps($serverUrl)
    {
        $response = (isset($_SERVER['HTTPS']) && substr($serverUrl, 0, strlen("http")) === "http") ? true : false;
        return $response;
    }
    private function checkConvertFile($module)
    {
        $ext = "txt";
        $to = "txt";
        $file = new File();
        $file->file_name = "testConvert." . $ext;

        $json = $module->convertService($file, 0, $to, false, true);
        $response = (empty($json["error"]) && $json["endConvert"]) ? true : false;

        return $response;
    }
}
