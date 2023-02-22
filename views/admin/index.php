<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE - Docs</strong> module configuration'); ?></div>

    <div class="panel-body">

        <?php if (empty($model->serverUrl) && empty($trial)): ?>
            <div class="alert alert-warning" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> not configured yet.'); ?></div>
        <?php elseif (!empty($model->settingError)): ?>
            <div class="alert alert-danger error" role="alert"><?= Yii::t('OnlyofficeModule.base', 'Error when trying to connect ({error})', ['error' => $model->settingError]); ?></div>
        <?php elseif (!empty($model->instaledVersion) && !$trial): ?>
            <div class="alert alert-success" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}', ['version' => $model->instaledVersion]); ?></div>
        <?php elseif (!empty($model->instaledVersion) && $trial): ?>
            <div class="alert alert-success" role="alert">
                <?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}', ['version' => $model->instaledVersion]); ?> 
                <p style = "color: #84be5e"><?= Yii::t('OnlyofficeModule.base', 'Trial period: {trial} days', ['trial' => $trial]); ?></p>
            </div>
        <?php endif; ?>

        <div class="alert alert-danger invalid-server-url" role="alert" hidden></div>

        <?php $form = ActiveForm::begin(['id' => 'configure-form']); ?>
        <div class="form-group">
            <?= $form->field($model, 'serverUrl'); ?>
            <?= $form->field($model, 'verifyPeerOff')->checkbox(); ?>
            <?= $form->field($model, 'forceSave')->checkbox(); ?>
            <?= $form->field($model, 'demoServer')->checkbox(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'jwtSecret'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'jwtHeader'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'internalServerUrl'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'storageUrl'); ?>
        </div>

        <div class="form-group">
            <?= Html::activeLabel($model,'customLabel', ['class' => 'control-label']); ?>
            <?= $form->field($model, 'chat')->checkbox(); ?>
            <?= $form->field($model, 'compactHeader')->checkbox(); ?>
            <?= $form->field($model, 'feedback')->checkbox(); ?>
            <?= $form->field($model, 'help')->checkbox(); ?>
            <?= $form->field($model, 'compactToolbar')->checkbox(); ?>
        </div>

        <div id="forceEditTypes" class="form-group">
            <?= Html::activeLabel($model,'editLabel', ['class' => 'control-label']); ?>
            <?php 
                foreach($forceEditExt as $key => $ext) {
                    echo $form->field($model, 'forceEditTypes[' . $ext . ']', ['options' => ['class' => 'checkbox-inline']])->checkbox(['label' => $ext]);
                }
            ?>
        </div>

        <div class="form-group">
            <?= Html::Button('Submit', ['id' => 'saveBtn', 'class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
    View::registerJs('
        humhub.module("onlyoffice", function (module, require, $) {

            $("#saveBtn").click(function(evt) {
                var saveBtnClone = $("#saveBtn").clone(true, false);

                var serverUrl = $("#configureform-serverurl").val();
                var verifyPeerOff = $("#configureform-verifypeeroff").prop("checked") ? 1 : 0;
                var forceSave = $("#configureform-forcesave").prop("checked") ? 1 : 0;
                var demoServer = $("#configureform-demoserver").prop("checked") ? 1 : 0;
                
                var jwtSecret = $("#configureform-jwtsecret").val();
                var jwtHeader = $("#configureform-jwtheader").val();
                var internalServerUrl = $("#configureform-internalserverurl").val();
                var storageUrl = $("#configureform-storageurl").val();

                var chat = $("#configureform-chat").prop("checked") ? 1 : 0;
                var compactHeader = $("#configureform-compactheader").prop("checked") ? 1 : 0;
                var feedback = $("#configureform-feedback").prop("checked") ? 1 : 0;
                var help = $("#configureform-help").prop("checked") ? 1 : 0;
                var compactToolbar = $("#configureform-compacttoolbar").prop("checked") ? 1 : 0;

                var forceEditTypes = {};
                var forceEditTypesNodes = $("#forceEditTypes").find("input[type=checkbox]");
                $.each(forceEditTypesNodes, function(i, node){
                    forceEditTypes[$(node).attr("id").replace("configureform-forceedittypes-", "")] = $(node).prop("checked") ? 1 : 0;
                });

                $.ajax({
                    url: "' . Url::to(["/onlyoffice/admin/save"]) . '",
                    cache: false,
                    type: "POST",
                    data: { 
                        "ConfigureForm": {
                            serverUrl: serverUrl,
                            verifyPeerOff: verifyPeerOff,
                            forceSave: forceSave,
                            demoServer: demoServer,
                            jwtSecret: jwtSecret,
                            jwtHeader: jwtHeader,
                            internalServerUrl: internalServerUrl,
                            storageUrl: storageUrl,
                            chat: chat,
                            compactHeader: compactHeader,
                            feedback: feedback,
                            help: help,
                            compactToolbar: compactToolbar,
                            forceEditTypes: forceEditTypes
                        }
                    },
                    dataType: "json"
                }).catch(function (e) {
                    $("#saveBtn").replaceWith(saveBtnClone);
                    module.log.error(e, true);
                })
            });
        });
    ');
?>