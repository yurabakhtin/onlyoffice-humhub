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
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\onlyoffice\models\ConfigureForm;
use humhub\modules\admin\components\Controller;

class AdminController extends Controller
{

    public function actionIndex()
    {
        $serverApiUrl = "";
        $model = new ConfigureForm();
        $model->loadSettings();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            $serverApiUrl = Yii::$app->getModule('onlyoffice')->getServerApiUrl();
        }

        $serverStatus = $this->getServerStatus();
        $response = $this->getDocumentServerVersion();
        $invalidHttps = $this->checkValidHttps($model->serverUrl);
        return $this->render('index', ['model' => $model, 'view' => $response, 'serverApiUrl' => $serverApiUrl, 'serverStatus' => $serverStatus, 'invalidHttps' => $invalidHttps]);
    }

    private function getDocumentServerVersion()
    {
        $module = Yii::$app->getModule('onlyoffice');
        return $module->commandService(['c' => 'version']);
    }

    private function getServerStatus()
    {
        $module = Yii::$app->getModule('onlyoffice');
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
}
