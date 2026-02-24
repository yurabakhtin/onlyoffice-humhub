<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\assets;

use humhub\components\assets\AssetBundle;
use Yii;

class Assets extends AssetBundle
{
    public $sourcePath = '@onlyoffice/resources';

    public $jsOptions = [
        'position' => \yii\web\View::POS_BEGIN,
    ];

    public function init()
    {
        $this->js = [
            Yii::$app->getModule('onlyoffice')->getServerApiUrl(),
            'humhub.onlyoffice.js',
        ];

        parent::init();
    }

    public $forceCopy = true;
}
