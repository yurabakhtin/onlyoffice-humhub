<?php

use humhub\libs\Html;
use humhub\widgets\ModalDialog;

\humhub\modules\onlydocuments\assets\Assets::register($this);
?>

<?php $modal = ModalDialog::begin(['header' => Yii::t('OnlydocumentsModule.base', '<strong>Convert</strong> document')]) ?>
<?= Html::beginTag('div', $options) ?>

<div class="modal-body">
    <span>
        <?= Yii::t('OnlydocumentsModule.base', 'Converting <strong>{oldFileName}</strong> to <strong>{newFileName}</strong>..',
        ['oldFileName' => $file->fileName, 'newFileName' => $newName]); ?>
    </span>
    <br/>
    <span id="oConvertMessage"></span>

</div>

<div class="modal-footer">
    <a href="#" data-modal-close class="btn btn-primary" data-action-click="close" data-ui-loader><?= Yii::t('OnlydocumentsModule.base', 'Close'); ?></a>
</div>

<?= Html::endTag('div'); ?>
<?php ModalDialog::end(); ?>