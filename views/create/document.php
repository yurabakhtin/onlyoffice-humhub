<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\libs\Html;
use humhub\modules\onlyoffice\assets\Assets;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\ModalDialog;

Assets::register($this);

$modal = ModalDialog::begin([
    'header' => Yii::t('OnlyofficeModule.base', '<strong>Create</strong> document'),
])
?>

<?php $form = ActiveForm::begin(); ?>

<div class="modal-body">
    <?= $form->field($model, 'fileName', ['template' => '{label}<div class="input-group">{input}<div class="input-group-addon">' . $model->extension . '</div></div>{hint}{error}']); ?>
    <?= $form->field($model, 'openFlag')->checkbox(); ?>

    <?= $form->field($model, 'fid')->hiddenInput()->label(false); ?>
</div>

<div class="modal-footer">
    <?= Html::submitButton('Save', ['data-action-click' => 'onlyoffice.createSubmit', 'data-ui-loader' => '', 'class' => 'btn btn-primary']); ?>
</div>

<?php ActiveForm::end(); ?>

<?php ModalDialog::end(); ?>
