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

        <?php if (!empty($view['version'])): ?>
            <div class="alert alert-success" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}, server status: {status}', ['version' => $view['version'], 'status' => $serverStatus]); ?></div>
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
            <div class="alert alert-danger invalid-server-url" role="alert" hidden><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> invalid hostname'); ?></div>
            <?= $form->field($model, 'verifyPeerOff')->checkbox(); ?>
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

        <?php 
            if(!empty($serverApiUrl)) {
                View::registerJsFile($serverApiUrl); 
                View::registerJs('
                    if(typeof DocsAPI === "undefined") {
                        $(".invalid-server-url").html("<strong>ONLYOFFICE Docs</strong> invalid hostname");
                        $(".invalid-server-url").show();
                    } 
                ');
            }

            if($invalidHttps) {
                View::registerJs(' 
                    $(".invalid-server-url").html("<strong>ONLYOFFICE Docs</strong> The hostname is not connected via <strong>http</strong> when using <strong>https</strong> on the hamhab server");
                    $(".invalid-server-url").show(); 
                ');
            }
        ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
