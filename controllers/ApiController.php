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
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->module = Yii::$app->getModule('onlyoffice');

        return parent::beforeAction($action);
    }

    /**
     * Saveas action
     */
    public function actionSaveas()
    {
        if (($body_data = file_get_contents('php://input')) === FALSE) {
            throw new \Exception('Empty body');
        }

        $data = json_decode($body_data, TRUE);
        if ($data === NULL) {
            throw new \Exception('Could not parse json');
        }

        $response = $this->module->request($data['url']);

        $newContent = $response->getBody();
        $headers = $response->getHeaders()->toArray();

        $file = new File();
        $file->file_name = $data['name'];
        $file->size = mb_strlen($newContent, '8bit');
        $file->mime_type = $headers['Content-Type'];
        $file->save();
        $file->getStore()->setContent($newContent);

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }
}