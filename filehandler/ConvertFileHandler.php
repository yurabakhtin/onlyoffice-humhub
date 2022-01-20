<?php

namespace humhub\modules\onlyoffice\filehandler;

use Yii;
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
            'label' => Yii::t('OnlyofficeModule.base', 'Convert document'),
            'data-action-url' => Url::to(['/onlyoffice/convert', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'onlyoffice-modal',
            'data-modal-close' => ''
        ];
    }

}
