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

        $newContent = $response->getContent();
        $fileExt = pathinfo($data['name'], PATHINFO_EXTENSION);

        $file = new File();
        $file->file_name = $data['name'];
        $file->size = mb_strlen($newContent, '8bit');
        $file->mime_type = $this->module->mimes[$fileExt];
        $file->save();
        $file->getStore()->setContent($newContent);

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }

        /**
     * Rename action
     */
    public function actionRename()
    {
        if (($body_data = file_get_contents('php://input')) === FALSE) {
            throw new \Exception('Empty body');
        }

        $data = json_decode($body_data, TRUE);
        if ($data === NULL) {
            throw new \Exception('Could not parse json');
        }

        $meta = [
            "c" => "meta",
            "key" => $data['key'],
            "meta"=> [
                "title" => $data['newFileName']
            ]
        ];

        $response = $this->module->commandService($meta);

        if($response['error'] !== 0){
            throw new \Exception('Error from command Service: ' . $response['error']);
        }

        $file = File::findOne(['onlyoffice_key' => $data['key']]);
        $ext = strtolower(FileHelper::getExtension($file));
        $file->updateAttributes(['file_name' => $data['newFileName'] . "." . $ext]);

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }
}