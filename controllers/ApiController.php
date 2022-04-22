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
use humhub\modules\onlyoffice\notifications\Mention;
use humhub\modules\user\models\User;
use yii\helpers\Url;

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

    public function actionSendNotify()
    {
        if (($body_data = file_get_contents('php://input')) === FALSE) {
            throw new \Exception('Empty body');
        }

        $data = json_decode($body_data, TRUE);
        if ($data === NULL) {
            throw new \Exception('Could not parse json');
        }

        $originator = Yii::$app->user->getIdentity();
        $users = User::find()->where(['email' => $data['emails']])->all();

        $comment = $data['comment'];
        $action = $data['ACTION_DATA'];

        $file = File::findOne(['onlyoffice_key' => $data['doc_key']]);

        Mention::instance()->from($originator)->about($file)->sendBulk($users);

        $url = Url::to(['/onlyoffice/open', 'guid' => $file->guid, 'mode' => 'view']);

        return $this->asJson([
            'url' => $url
        ]);
    }
}