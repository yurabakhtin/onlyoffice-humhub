<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlydocuments\widgets;

use Yii;
use yii\web\HttpException;
use humhub\modules\file\models\File;
use yii\helpers\Url;
use humhub\libs\Html;
use humhub\modules\file\libs\FileHelper;
use humhub\widgets\JsWidget;
use \Firebase\JWT\JWT;

/**
 * Description of EditorWidget
 *
 * @author Luke
 */
class EditorWidget extends JsWidget
{

    /**
     * @var File the file
     */
    public $file;

    /**
     * @var string mode (edit or view)
     */
    public $mode;

    /**
     * @inheritdoc
     */
    public $jsWidget = 'onlydocuments.Editor';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritdoc
     */
    protected $documentType = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $module = Yii::$app->getModule('onlydocuments');
        $this->documentType = $module->getDocumentType($this->file);
        if ($this->documentType === null) {
            throw new HttpException('400', 'Requested file type is not supported!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $module = Yii::$app->getModule('onlydocuments');

        return [
            'config' => $this->getConfig(),
            'module-configured' => (empty($module->getServerUrl()) ? '0' : '1'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'style' => 'height:100%;border-radius: 8px 8px 0px 0px;background-color:#F4F4F4'
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('editor', [
                    'documentType' => $this->documentType,
                    'file' => $this->file,
                    'mode' => $this->mode,
                    'options' => $this->getOptions(),
        ]);
    }

    /**
     * Generate unique document key
     * 
     * @return string
     */
    protected function generateDocumentKey()
    {
        if (!empty($this->file->onlydocuments_key)) {
            return $this->file->onlydocuments_key;
        }

        $key = substr(strtolower(md5(Yii::$app->security->generateRandomString(20))), 0, 20);
        $this->file->updateAttributes(['onlydocuments_key' => $key]);
        return $key;
    }

    protected function getConfig()
    {
        $module = Yii::$app->getModule('onlydocuments');
        $user = Yii::$app->user->getIdentity();
        $key = $this->generateDocumentKey($this->file);

        $config = [
            'type' => 'desktop',
            'documentType' => $this->documentType,
            'document' => [
                'title' => Html::encode($this->file->fileName),
                'url' => Url::to(['/onlydocuments/backend/download', 'key' => $key], true),
                'fileType' => Html::encode(strtolower(FileHelper::getExtension($this->file))),
                'key' => $key,
                'info' => [
                    'author' => Html::encode($this->file->createdBy->displayname),
                    'created' => Html::encode(Yii::$app->formatter->asDatetime($this->file->created_at, 'short')),
                ],
                'permissions' => [
                    'edit' => $this->mode == 'edit',
                    'download' => true,
                ]
            ],
            'editorConfig' => [
                'mode' => $this->mode,
                'lang' => ($user) && !empty($user->language) ? $user->language : Yii::$app->language,
                'callbackUrl' => Url::to(['/onlydocuments/backend/track', 'key' => $key], true),
                'user' => [
                    'id' => ($user) ? Html::encode($user->guid) : '',
                    'name' => ($user) ? Html::encode($user->displayname) : 'Anonymous User',
                ],
                'embedded' => [
                    'toolbarDocked' => 'top',
                ],
                'customization' => [
                    'about' => false,
                    'feedback' => false,
                    'autosave' => true,
                    'forcesave' => true,
                ]
            ]
        ];

        if ($module->isJwtEnabled()) {
            $token = JWT::encode($config, $module->getJwtSecret());
            $config['token'] = $token;
        }

        return $config;
    }
}
