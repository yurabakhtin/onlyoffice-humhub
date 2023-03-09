<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\libs\Html;
use humhub\widgets\ModalDialog;

\humhub\modules\onlyoffice\assets\Assets::register($this);
?>

<?php $modal = ModalDialog::begin(['header' => Yii::t('OnlyofficeModule.base', '<strong>Convert</strong> document')]) ?>
<?= Html::beginTag('div', $options) ?>

<div class="modal-body">
    <span>
        <?= Yii::t('OnlyofficeModule.base', 'Converting <strong>{oldFileName}</strong> to <strong>{newFileName}</strong>..',
        ['oldFileName' => $file->fileName, 'newFileName' => $newName]); ?>
    </span>
    <br/>
    <span id="oConvertMessage"></span>

</div>

<div class="modal-footer">
    <a href="#" data-modal-close class="btn btn-primary" data-action-click="close" data-ui-loader><?= Yii::t('OnlyofficeModule.base', 'Close'); ?></a>
</div>

<?= Html::endTag('div'); ?>
<?php ModalDialog::end(); ?>