<?php

namespace humhub\modules\onlydocuments\controllers;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\onlydocuments\Module;
use humhub\modules\onlydocuments\components\BaseFileController;

class ConvertController extends BaseFileController
{

    public function init()
    {
        parent::init();

        $module = Yii::$app->getModule('onlydocuments');

        if (!$module->canConvert($this->file)) {
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
        $module = Yii::$app->getModule('onlydocuments');

        $json = $module->convertService($this->file, $ts);

        if (!empty($json["endConvert"]) && $json["endConvert"]) {
            $this->saveFileReplace($json["fileUrl"], $newName);
        }

        return $json;
    }

    private function saveFileReplace($url, $newName) {
        $content = file_get_contents($url);

        $this->file->store->setContent($content);
        $this->file->updateAttributes([
            'onlydocuments_key' => new \yii\db\Expression('NULL'),
            'size' => strlen($content),
            'file_name' => $newName
        ]);
    }
}
