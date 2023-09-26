<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\Events;

return [
    'id' => 'onlyoffice',
    'class' => 'humhub\modules\onlyoffice\Module',
    'namespace' => 'humhub\modules\onlyoffice',
    'events' => [
        [FileHandlerCollection::class, FileHandlerCollection::EVENT_INIT, [Events::class, 'onFileHandlerCollection']],
        [File::class, File::EVENT_AFTER_NEW_STORED_FILE, [Events::class, 'onAfterNewStoredFile']],
    ]
];
