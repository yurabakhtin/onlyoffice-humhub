<?php

namespace humhub\modules\onlydocuments\widgets;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\onlydocuments\models\Share;
use humhub\modules\onlydocuments\Module;
use humhub\widgets\JsWidget;

class ConvertWidget extends JsWidget
{

    /**
     * @var File the file
     */
    public $file;

    public $newName;

    /**
     * @inheritdoc
     */
    public $jsWidget = 'onlydocuments.Convert';

    /**
     * @inheritdoc
     */
    public $init = true;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $module = Yii::$app->getModule('onlydocuments');
        $this->newName = substr($this->file->fileName, 0, strpos($this->file->fileName, '.') + 1) . $module->convertsTo[strtolower(FileHelper::getExtension($this->file))];
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'convert-post' => Url::to(['/onlydocuments/convert/convert', 'guid' => $this->file->guid, 'ts' => time(), 'newName' => $this->newName]),
            'file-info-url' => Url::to(['/onlydocuments/open/get-info', 'guid' => $this->file->guid]),
            'done-message' => Yii::t('OnlydocumentsModule.base', 'Done!'),
            'error-message' => Yii::t('OnlydocumentsModule.base', 'Error:'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('convert', [
                    'options' => $this->getOptions(),
                    'file' => $this->file,
                    'newName' => $this->newName,
        ]);
    }

}
