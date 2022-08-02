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
use Exception;
use yii\helpers\Url;
use humhub\components\Controller;
use \humhub\components\Module;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\user\models\User;
use humhub\modules\onlyoffice\notifications\Mention as Notify;
use humhub\modules\onlyoffice\models\Mention;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\cfiles\permissions\ManageFiles;

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

    public function actionUsersForMentions()
    {
        $usersForMentions = [];
        $curUser = Yii::$app->user->getIdentity();
        $users = User::find()->all();

        foreach ($users as $user) {
            if ($user->id != $curUser->id && $user->profile->firstname != null && $user->profile->lastname != null && $user->email != null) {
                array_push($usersForMentions,[
                    "name" => $user->profile->firstname . " " . $user->profile->lastname,
                    "email" => $user->email
                ]);
            }
        }

        return $this->asJson([
            'usersForMentions' => $usersForMentions
        ]);
    }

    public function actionMakeAnchor()
    {
        if (($body_data = file_get_contents('php://input')) === FALSE) {
            throw new \Exception('Empty body');
        }

        $data = json_decode($body_data, TRUE);
        if ($data === NULL) {
            throw new \Exception('Could not parse json');
        }

        $file = File::findOne(['onlyoffice_key' => $data['doc_key']]);
        $url = Url::to(['/onlyoffice/open', 'guid' => $file->guid, 'mode' => 'view']);
        
        return $this->asJson([
            'url' => $url
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

        $message = $data['comment'];
        $anchor = $data['ACTION_DATA'];

        $file = File::findOne(['onlyoffice_key' => $data['doc_key']]);

        $mention = Mention::generateMention($file, $message, $anchor);

        try{
            Notify::instance()->from($originator)->about($mention)->sendBulk($users);
        } catch(Exception $exception) {
            throw new Exception("Mention error.");
        }

        return $this->asJson([
            'file' => $file
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

        $file = File::findOne(['onlyoffice_key' => $data['key']]);
        
        $owner = User::findOne($file->created_by);
        $containerRecord = ContentContainer::findOne(['id' => $owner->contentcontainer_id]);
        $container = $containerRecord->getPolymorphicRelation();
        $canRename = $container->can(ManageFiles::class);
        if(!$canRename) {
            throw new \Exception('Permission denied');
        }

        $newFileName = $data['newFileName'];
        $origExt = $data['ext'];
        $arrayName = explode(".", $newFileName);
        $curExt = end($arrayName);

        if($origExt !== $curExt) {
            $newFileName .= "." . $origExt;
        }

        $file->updateAttributes(['file_name' => $newFileName]);

        $meta = [
            "c" => "meta",
            "key" => $data['key'],
            "meta"=> [
                "title" => $newFileName
            ]
        ];
        $response = $this->module->commandService($meta);

        if($response['error'] !== 0){
            throw new \Exception('Error from command Service: ' . $response['error']);
        }

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }
}