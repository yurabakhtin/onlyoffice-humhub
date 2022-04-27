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
use humhub\modules\onlyoffice\notifications\Mention as Notify;
use humhub\modules\onlyoffice\models\Mention;
use humhub\modules\user\models\User;
use Exception;

class MentionController extends Controller
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

        $message = $data['comment'];
        $anchor = $data['ACTION_DATA'];

        $file = File::findOne(['onlyoffice_key' => $data['doc_key']]);

        $mention = Mention::generateMention($file, $message, $anchor);

        try{
            Notify::instance()->from($originator)->about($mention)->sendBulk($users);
        } catch(Exception $exeption) {
            throw new Exception("Mention error.");
        }

        return $this->asJson([
            'file' => $file
        ]);

    }
}