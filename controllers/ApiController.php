<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\file\models\File;
use humhub\components\Controller;
use \humhub\components\Module;
use humhub\modules\file\libs\FileHelper;

class ApiController extends Controller
{
    /**
     * @var Module
     */
    public $module;

    /**
     * Saveas function for the Only server
     */
    public function actionSaveas()
    {
        $this->module = Yii::$app->getModule('onlyoffice');

        if (($body_stream = file_get_contents('php://input')) === FALSE) {
            throw new \Exception('Empty body');
        }

        $data = json_decode($body_stream, TRUE);
        if ($data === NULL) {
            throw new \Exception('Could not parse json');
        }

        $response = $this->module->request($data["url"]);

        $newContent = $response->getBody();
        $headers = $response->getHeaders()->toArray();

        $file = new File();
        $file->file_name = $data["name"];
        $file->size = $headers['Content-Length'];
        $file->mime_type = $headers['Content-Type'];
        $file->save();
        $file->getStore()->setContent($newContent);

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }
}