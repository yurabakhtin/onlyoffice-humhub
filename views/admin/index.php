<?php

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE - Docs</strong> module configuration'); ?></div>

    <div class="panel-body">

        <?php if ($trial >= 0 && !empty($view['version'])): ?>
            <div class="alert alert-success" role="alert">
                <?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}, trial period: {trial} days', ['version' => $view['version'], 'trial' => $trial]); ?>
            </div>
        <?php elseif (!empty($view['version'])): ?>
            <div class="alert alert-success" role="alert">
                <?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}', ['version' => $view['version']]); ?>
            </div>
        <?php elseif (empty($model->serverUrl)): ?>
            <div class="alert alert-warning" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> not configured yet.'); ?></div>
        <?php elseif (!empty($view['error']) && $view['error'] == 6): ?>
            <div class="alert alert-danger" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> invalid JWT token.'); ?></div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> not accessible.'); ?></div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'configure-form']); ?>
        <div class="form-group">
            <?= $form->field($model, 'serverUrl'); ?>
            <?= $form->field($model, 'verifyPeerOff')->checkbox(); ?>
            <?= $form->field($model, 'demoServer')->checkbox(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'jwtSecret'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'internalServerUrl'); ?>
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
<?php
   if($trial === false) {
    View::registerJs('
        $("#configureform-demoserver").closest("label").css({"cursor":"default", "opacity":"0.5"});
        $("#configureform-demoserver").attr("checked", false);
        $("#configureform-demoserver").attr("disabled", true);
    ');
   } 
?>