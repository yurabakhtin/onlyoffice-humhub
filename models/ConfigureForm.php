<?php

namespace humhub\modules\onlyoffice\models;

use Yii;

/**
 * ConfigureForm defines the configurable fields.

 */
class ConfigureForm extends \yii\base\Model
{

    public $serverUrl;
    public $jwtSecret;
    public $storageUrl;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['serverUrl', 'string'],
            ['jwtSecret', 'string'],
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
            'jwtSecret' => Yii::t('OnlyofficeModule.base', 'JWT Secret'),
            'storageUrl' => Yii::t('OnlyofficeModule.base', 'Server address for internal requests from the Document Editing Service'),
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
            'storageUrl' => Yii::t('OnlyofficeModule.base', 'e.g. http://storage'),
        ];
    }

    public function loadSettings()
    {
        $this->serverUrl = Yii::$app->getModule('onlyoffice')->settings->get('serverUrl');
        $this->jwtSecret = Yii::$app->getModule('onlyoffice')->settings->get('jwtSecret');

        return true;
    }

    public function save()
    {
        Yii::$app->getModule('onlyoffice')->settings->set('serverUrl', rtrim($this->serverUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('jwtSecret', $this->jwtSecret);
        Yii::$app->getModule('onlyoffice')->settings->set('storageUrl', rtrim($this->storageUrl, '/'));

        return true;
    }

}
