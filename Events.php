<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice;

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\onlyoffice\permissions\CanUseOnlyOffice;
use Yii;

/**
 * @author luke
 */
class Events
{

    public static function onFileHandlerCollection($event)
    {
        if (!Yii::$app->user->can(CanUseOnlyOffice::class)) {
            return;
        }

        /* @var $collection FileHandlerCollection */
        $collection = $event->sender;

        if ($collection->type === FileHandlerCollection::TYPE_CREATE) {
            $collection->register(new filehandler\CreateFileHandler());
            return;
        }

        /* @var $module \humhub\modules\onlyoffice\Module */
        $module = Yii::$app->getModule('onlyoffice');
        $file = $event->sender->file;

        if ($module->getDocumentType($file) !== null) {
            $canEdit = $collection->type == FileHandlerCollection::TYPE_EDIT && $module->canEdit($file);
            $canConvert = $collection->type == FileHandlerCollection::TYPE_EDIT && $module->canConvert($file);
            $canView = $collection->type == FileHandlerCollection::TYPE_VIEW && $module->canView($file);

            if ($canEdit) {
                $collection->register(new filehandler\EditFileHandler());
            }
            if ($canConvert) {
                $collection->register(new filehandler\ConvertFileHandler());
            }
            if ($canView) {
                $collection->register(new filehandler\ViewFileHandler());
            }
        }
    }

}
