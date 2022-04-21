<?php

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

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
    public $demoServer;

    public $chat;
    public $compactHeader;
    public $feedback;
    public $help;
    public $compactToolbar;
    public $customLabel;

    public $editCSV;
    public $editODP;
    public $editODS;
    public $editODT;
    public $editRTF;
    public $editTXT;

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
            ['demoServer', 'boolean'],
            ['chat', 'boolean'],
            ['compactHeader', 'boolean'],
            ['feedback', 'boolean'],
            ['help', 'boolean'],
            ['compactToolbar', 'boolean'],
            ['editCSV', 'boolean'],
            ['editODP', 'boolean'],
            ['editODS', 'boolean'],
            ['editODT', 'boolean'],
            ['editRTF', 'boolean'],
            ['editTXT', 'boolean'],
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
            'demoServer' => Yii::t('OnlyofficeModule.base', 'Connect to demo ONLYOFFICE Docs server'),
            'chat' => Yii::t('OnlyofficeModule.base', 'Display Chat menu button'),
            'compactHeader' => Yii::t('OnlyofficeModule.base', 'Display the header more compact'),
            'feedback' => Yii::t('OnlyofficeModule.base', 'Display Feedback & Support menu button'),
            'help' => Yii::t('OnlyofficeModule.base', 'Display Help menu button'),
            'compactToolbar' => Yii::t('OnlyofficeModule.base', 'Display monochrome toolbar header'),
            'customLabel' => Yii::t('OnlyofficeModule.base', 'The customization section allows personalizing the editor interface'),
            'editLabel' => Yii::t('OnlyofficeModule.base', 'Open the file for editing (due to format restrictions, the data might be lost when saving to the formats from the list below)'),
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
            'demoServer' => Yii::t('OnlyofficeModule.base', 'This is a public test server, please do not use it for private sensitive data. The server will be available during a 30-day period.'),
        ];
    }

    public function loadSettings()
    {
        $this->serverUrl = Yii::$app->getModule('onlyoffice')->settings->get('serverUrl');
        $this->verifyPeerOff = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('verifyPeerOff');
        $this->jwtSecret = Yii::$app->getModule('onlyoffice')->settings->get('jwtSecret');
        $this->internalServerUrl = Yii::$app->getModule('onlyoffice')->settings->get('internalServerUrl');
        $this->storageUrl = Yii::$app->getModule('onlyoffice')->settings->get('storageUrl');
        $this->demoServer = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('demoServer');
        $this->chat = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('chat');
        $this->compactHeader = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('compactHeader');
        $this->feedback = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('feedback');
        $this->help = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('help');
        $this->compactToolbar = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('compactToolbar');
        $this->editCSV = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('editCSV');
        $this->editODP = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('editODP');
        $this->editODS = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('editODS');
        $this->editODT = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('editODT');
        $this->editRTF = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('editRTF');
        $this->editTXT = (boolean)Yii::$app->getModule('onlyoffice')->settings->get('editTXT');

        return true;
    }

    public function save()
    {
        Yii::$app->getModule('onlyoffice')->settings->set('serverUrl', rtrim($this->serverUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('verifyPeerOff', $this->verifyPeerOff);
        Yii::$app->getModule('onlyoffice')->settings->set('jwtSecret', $this->jwtSecret);
        Yii::$app->getModule('onlyoffice')->settings->set('internalServerUrl', rtrim($this->internalServerUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('storageUrl', rtrim($this->storageUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('demoServer', $this->demoServer);
        Yii::$app->getModule('onlyoffice')->settings->set('chat', $this->chat);
        Yii::$app->getModule('onlyoffice')->settings->set('compactHeader', $this->compactHeader);
        Yii::$app->getModule('onlyoffice')->settings->set('feedback', $this->feedback);
        Yii::$app->getModule('onlyoffice')->settings->set('help', $this->help);
        Yii::$app->getModule('onlyoffice')->settings->set('compactToolbar', $this->compactToolbar);
        Yii::$app->getModule('onlyoffice')->settings->set('editCSV', $this->editCSV);
        Yii::$app->getModule('onlyoffice')->settings->set('editODP', $this->editODP);
        Yii::$app->getModule('onlyoffice')->settings->set('editODS', $this->editODS);
        Yii::$app->getModule('onlyoffice')->settings->set('editODT', $this->editODT);
        Yii::$app->getModule('onlyoffice')->settings->set('editRTF', $this->editRTF);
        Yii::$app->getModule('onlyoffice')->settings->set('editTXT', $this->editTXT);

        return true;
    }

}
