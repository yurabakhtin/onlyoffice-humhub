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
use humhub\modules\user\models\User;
use humhub\components\Controller;
use \Firebase\JWT\JWT;

class BackendController extends Controller
{
    /**
     * @inheritdoc
     * Allow server to server configuration without any authentication
     */
    public $access = \humhub\components\access\ControllerAccess::class;

    /**
     * @var File
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        $key = Yii::$app->request->get('key');
        $this->file = File::findOne(['onlyoffice_key' => $key]);

        if ($this->file == null) {
            throw new HttpException(404, Yii::t('OnlyofficeModule.base', 'Could not find requested file!'));
        }

        return parent::beforeAction($action);
    }

    /**
     * Download function for the Only server
     */
    public function actionDownload()
    {
        //Yii::warning("Downloading file guid: " . $this->file->guid, 'onlyoffice');
        return Yii::$app->response->sendFile($this->file->store->get(), $this->file->file_name);
    }

    public function actionTrack()
    {
        Yii::$app->response->format = 'json';

        $_trackerStatus = array(
            0 => 'NotFound',
            1 => 'Editing',
            2 => 'MustSave',
            3 => 'Corrupted',
            4 => 'Closed',
            6 => 'ForceSave'
        );

        $result = [];
        $msg = null;
        try {
            if (($body_stream = file_get_contents('php://input')) === FALSE) {
                throw new \Exception('Empty body');
            }

            $data = json_decode($body_stream, TRUE); //json_decode - PHP 5 >= 5.2.0
            if ($data === NULL) {
                throw new \Exception('Could not parse json');
            }

            $module = Yii::$app->getModule('onlyoffice');
            if ($module->isJwtEnabled()) {
                $token = null;
                if (!empty($data["token"])) {
                    $token = $data["token"];
                } else {
                    $header = Yii::$app->request->headers->get('Authorization');
                    if (!empty($header)) {
                        $token = substr($header, strlen('Bearer '));
                    }
                }

                if (empty($token)) {
                    throw new \Exception('Expected JWT');
                }

                try {
                    $ds = JWT::decode($token, $module->getJwtSecret(), array('HS256'));
                    $data = isset($ds->payload) ? (array)$ds->payload : (array)$ds;
                } catch (\Exception $ex) {
                    throw new \Exception('Invalid JWT signature');
                }
            }

            //Yii::warning('Tracking request for file ' . $this->file->guid . ' - data: ' . print_r($data, 1), 'onlyoffice');

            $user = null;
            if (!empty($data['users'])) {
                $users = $data['users'];
                $user = User::findOne(['guid' => $users[0]]);
            }

            $status = $_trackerStatus[$data["status"]];
            switch ($status) {
                case "MustSave":
                case "Corrupted":
                case "ForceSave":

                    $newData = file_get_contents($data["url"]);

                    if (!empty($newData)) {

                        if (version_compare(Yii::$app->version, '1.10', '=>')) {
                            // For HumHub from version 1.10 with versioning support
                            $this->file->setStoredFileContent($newData);
                        } else {
                            // Older HumHub versions
                            $this->file->getStore()->setContent($newData);
                        }

                        if ($status != 'ForceSave') {
                            $newAttr = [
                                'onlyoffice_key' => new \yii\db\Expression('NULL'),
                                'updated_at' => date("Y-m-d H:i:s"),
                                'size' => strlen($newData),
                            ];
                            if (!empty($user)) $newAttr['updated_by'] = $user->getId();

                            $this->file->updateAttributes($newAttr);
                            //Yii::warning('Dosaved', 'onlyoffice');
                        } else {
                            //Yii::warning('ForceSaved', 'onlyoffice');
                        }
                    } else {
                        throw new \Exception('Could not save onlyoffice document: ' . $data["url"]);
                    }

                    break;
            }

        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'onlyoffice');
            $msg = $e->getMessage();
        }

        if ($msg == null) {
            $result['error'] = 0;
        } else {
            Yii::$app->response->statusCode = 500;
            $result['error'] = 1;
            $result['message'] = $msg;
        }

        //Yii::warning("Return: " . print_r($result, 1), 'onlyoffice');
        return $result;
    }

}
