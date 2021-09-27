<?php

namespace humhub\modules\onlyoffice\models;

use Yii;

/**
 * ConfigureForm defines the configurable fields.

 */
class ConfigureForm extends \yii\base\Model
{

    public $serverUrl;
    public $verifyPeerOff;
    public $jwtSecret;
    public $internalServerUrl;
    public $storageUrl;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['serverUrl', 'string'],
            ['verifyPeerOff', 'boolean'],
            ['jwtSecret', 'string'],
            ['internalServerUrl', 'string'],
            ['storageUrl', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'serverUrl' => Yii::t('OnlyofficeModule.base', 'Hostname'),
            'verifyPeerOff' => Yii::t('OnlyofficeModule.base', 'Disable certificate verification (insecure)'),
            'jwtSecret' => Yii::t('OnlyofficeModule.base', 'JWT Secret'),
            'internalServerUrl' => Yii::t('OnlyofficeModule.base', 'ONLYOFFICE Docs address for internal requests from the server'),
            'storageUrl' => Yii::t('OnlyofficeModule.base', 'Server address for internal requests from ONLYOFFICE Docs'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'serverUrl' => Yii::t('OnlyofficeModule.base', 'e.g. http://documentserver'),
            'jwtSecret' => Yii::t('OnlyofficeModule.base', 'JWT Secret key (leave blank to disable)'),
            'internalServerUrl' => Yii::t('OnlyofficeModule.base', 'e.g. http://documentserver'),
            'storageUrl' => Yii::t('OnlyofficeModule.base', 'e.g. http://storage'),
        ];
    }

    public function loadSettings()
    {
        $this->serverUrl = Yii::$app->getModule('onlyoffice')->settings->get('serverUrl');
        $this->verifyPeerOff = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('verifyPeerOff');
        $this->jwtSecret = Yii::$app->getModule('onlyoffice')->settings->get('jwtSecret');
        $this->internalServerUrl = Yii::$app->getModule('onlyoffice')->settings->get('internalServerUrl');

        return true;
    }

    public function save()
    {
        Yii::$app->getModule('onlyoffice')->settings->set('serverUrl', rtrim($this->serverUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('verifyPeerOff', $this->verifyPeerOff);
        Yii::$app->getModule('onlyoffice')->settings->set('jwtSecret', $this->jwtSecret);
        Yii::$app->getModule('onlyoffice')->settings->set('internalServerUrl', rtrim($this->internalServerUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('storageUrl', rtrim($this->storageUrl, '/'));

        return true;
    }

}
