<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlyoffice\permissions;

use humhub\libs\BasePermission;
use Yii;

/**
 * CanUseOnlyOffice Permissions
 */
class CanUseOnlyOffice extends BasePermission
{

    /**
     * @inheritdoc
     */
    protected $moduleId = 'onlyoffice';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('OnlyofficeModule.base', 'Can use ONLYOFFICE');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('OnlyofficeModule.base', 'Allows the user to use ONLYOFFICE.');
    }

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;

}
