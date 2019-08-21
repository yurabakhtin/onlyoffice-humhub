<?php

namespace humhub\modules\onlydocuments\filehandler;

use Yii;
use humhub\libs\Html;
use humhub\modules\onlydocuments\Module;
use humhub\modules\file\handler\BaseFileHandler;
use yii\helpers\Url;

class ConvertFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('FileModule.base', 'Convert document'),
            'data-action-url' => Url::to(['/onlydocuments/convert', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'onlydocuments-modal',
            'data-modal-close' => ''
        ];
    }

}
