<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE - DocumentServer</strong> module configuration'); ?></div>

    <div class="panel-body">

        <?php if (!empty($view['version'])): ?>
            <div class="alert alert-success" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>DocumentServer</strong> successfully connected! - Installed version: {version}', ['version' => $view['version']]); ?></div>
        <?php elseif (empty($model->serverUrl)): ?>
            <div class="alert alert-warning" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>DocumentServer</strong> not configured yet.'); ?></div>
        <?php elseif (!empty($view['error']) && $view['error'] == 6): ?>
            <div class="alert alert-danger" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>DocumentServer</strong> invalid JWT token.'); ?></div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>DocumentServer</strong> not accessible.'); ?></div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'configure-form']); ?>
        <div class="form-group">
            <?= $form->field($model, 'serverUrl'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'jwtSecret'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'storageUrl'); ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
