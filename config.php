<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\modules\file\handler\FileHandlerCollection;

return [
    'id' => 'onlyoffice',
    'class' => 'humhub\modules\onlyoffice\Module',
    'namespace' => 'humhub\modules\onlyoffice',
    'events' => [
        [FileHandlerCollection::className(), FileHandlerCollection::EVENT_INIT, ['humhub\modules\onlyoffice\Events', 'onFileHandlerCollection']],
    ],
];
