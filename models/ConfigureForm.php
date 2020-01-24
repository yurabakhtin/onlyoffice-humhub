<?php

namespace humhub\modules\onlydocuments\models;

use Yii;

/**
 * ConfigureForm defines the configurable fields.

 */
class ConfigureForm extends \yii\base\Model
{

    public $serverUrl;
    public $jwtSecret;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['serverUrl', 'string'],
            ['jwtSecret', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'serverUrl' => Yii::t('OnlydocumentsModule.base', 'Hostname'),
            'jwtSecret' => Yii::t('OnlydocumentsModule.base', 'JWT Secret'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'serverUrl' => Yii::t('OnlydocumentsModule.base', 'e.g. http://documentserver'),
            'jwtSecret' => Yii::t('OnlydocumentsModule.base', 'JWT Secret key (leave blank to disable)'),
        ];
    }

    public function loadSettings()
    {
        $this->serverUrl = Yii::$app->getModule('onlydocuments')->settings->get('serverUrl');
        $this->jwtSecret = Yii::$app->getModule('onlydocuments')->settings->get('jwtSecret');

        return true;
    }

    public function save()
    {
        Yii::$app->getModule('onlydocuments')->settings->set('serverUrl', rtrim($this->serverUrl, '/'));
        Yii::$app->getModule('onlydocuments')->settings->set('jwtSecret', $this->jwtSecret);

        return true;
    }

}
