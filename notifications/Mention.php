<?php

namespace humhub\modules\onlyoffice\notifications;


use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use humhub\modules\file\models\File;
use humhub\components\SocialActivity;

class Mention extends BaseNotification
{
    public $moduleId = "onlyoffice";

    public $viewName = "mentioned";

    public $file;

    public function html()
    {
        return Yii::t('UserModule.notification', '{displayName} mentioned you in {contentTitle}.', [
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $this->source->file_name
        ]);
    }

    public function getViewParams($params = [])
    {
        if ($this->hasContent() && $this->getContent()->updated_at instanceof Expression) {
            $this->getContent()->refresh();
            $date = $this->getContent()->updated_at;
        } elseif ($this->hasContent()) {
            $date = $this->getContent()->updated_at;
        } else {
            $date = null;
        }

        $this->file = File::findOne($this->record->source_pk);

        $url = Url::to(['/onlyoffice/open', 'guid' => $this->file->guid, 'mode' => 'view']);
        $relativeUrl = Url::to(['/onlyoffice/open', 'guid' => $this->file->guid, 'mode' => 'view']);

        $result = [
            'url' => $url,
            'relativeUrl' => $relativeUrl,
            'date' => $date,
            'isNew' => !$this->record->seen,
        ];

        return ArrayHelper::merge(SocialActivity::getViewParams($result), $params);
    }
}