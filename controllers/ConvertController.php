<?php

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\onlyoffice\components\BaseFileController;
use \humhub\components\Module;

class ConvertController extends BaseFileController
{

    /**
     * @var Module
     */
    public $module;

    public function init()
    {
        parent::init();

        $this->module = Yii::$app->getModule('onlyoffice');

        if (!$this->module->canConvert($this->file)) {
            throw new HttpException('400', 'Could not convert this file');
        }
    }

    public function actionIndex()
    {
        return $this->renderAjax('index', ['file' => $this->file]);
    }

    public function actionConvert($guid, $ts, $newName)
    {
        Yii::$app->response->format = 'json';

        $json = $this->module->fileToConversion($this->file, $ts);

        if (!empty($json["endConvert"]) && $json["endConvert"]) {
            $this->saveFileReplace($json["fileUrl"], $newName);
        }

        return $json;
    }

    private function saveFileReplace($url, $newName) {
        $content = $this->module->request($url)->getBody();

        $this->file->store->setContent($content);
        $this->file->updateAttributes([
            'onlyoffice_key' => new \yii\db\Expression('NULL'),
            'size' => strlen($content),
            'file_name' => $newName
        ]);
    }
}
