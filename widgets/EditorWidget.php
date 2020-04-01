<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlyoffice\widgets;

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
    public $jsWidget = 'onlyoffice.Editor';

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
        $module = Yii::$app->getModule('onlyoffice');
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
        $module = Yii::$app->getModule('onlyoffice');

        return [
            'config' => $this->getConfig(),
            'edit-mode' => $this->mode,
            'file-info-url' => Url::to(['/onlyoffice/open/get-info', 'guid' => $this->file->guid]),
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

    protected function getConfig()
    {
        $module = Yii::$app->getModule('onlyoffice');
        $user = Yii::$app->user->getIdentity();
        $key = $module->generateDocumentKey($this->file);

        $config = [
            'type' => 'desktop',
            'documentType' => $this->documentType,
            'document' => [
                'title' => Html::encode($this->file->fileName),
                'url' => Url::to(['/onlyoffice/backend/download', 'key' => $key], true),
                'fileType' => Html::encode(strtolower(FileHelper::getExtension($this->file))),
                'key' => $key,
                'info' => [
                    'author' => Html::encode($this->file->createdBy->displayname),
                    'created' => Html::encode(Yii::$app->formatter->asDatetime($this->file->created_at, 'short')),
                ],
                'permissions' => [
                    'edit' => $this->mode == 'edit',
                ]
            ],
            'editorConfig' => [
                'mode' => $this->mode,
                'lang' => ($user) && !empty($user->language) ? $user->language : Yii::$app->language,
                'callbackUrl' => Url::to(['/onlyoffice/backend/track', 'key' => $key], true),
                'user' => [
                    'id' => ($user) ? Html::encode($user->guid) : '',
                    'name' => ($user) ? Html::encode($user->displayname) : 'Anonymous User',
                ],
                'customization' => [
                    //'forcesave' => true,
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
