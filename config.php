<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\modules\content\models\Content;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\onlyoffice\Events;

return [
    'id' => 'onlyoffice',
    'class' => 'humhub\modules\onlyoffice\Module',
    'namespace' => 'humhub\modules\onlyoffice',
    'events' => [
        [FileHandlerCollection::class, FileHandlerCollection::EVENT_INIT, [Events::class, 'onFileHandlerCollection']],
        [Content::class, Content::EVENT_AFTER_SOFT_DELETE, [Events::class, 'onContentAfterSoftDelete']],
    ]
];
