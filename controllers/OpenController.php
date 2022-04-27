<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\onlyoffice\components\BaseFileController;

class OpenController extends BaseFileController
{
    /**
     * @inheritdoc
     * Allow access to this controller without any authentication (guest access)
     */
    public $access = \humhub\components\access\ControllerAccess::class;
    public $anchor;
    public $actionDataUrl;
    public $actionData;
    /**
     * Opens the document in modal
     * 
     * @return string
     * @throws HttpException
     */
    public function actionIndex()
    {
        $url = Yii::$app->request->url;
        if(str_contains($url, 'anchor')) {
            $this->actionDataUrl = str_replace('anchor=', '', strstr($url, 'anchor'));
            $this->actionData = urldecode($this->actionDataUrl);
            $this->anchor = json_decode($this->actionData, true);
            do {
                $this->actionData = urldecode($this->actionData);
                $this->anchor = json_decode($this->actionData, true);
            } while (!$this->anchor);
        }
        if (!Yii::$app->request->isAjax || str_contains($url, 'notify')) {
            return $this->redirectToModal();
        }

        return $this->renderAjax('index', [
                    'file' => $this->file,
                    'mode' => $this->mode,
                    'anchor' => $this->anchor
        ]);
    }

    /**
     * Returns file informations
     * 
     * @return type
     * @throws HttpException
     */
    public function actionGetInfo()
    {
        return $this->asJson(['file' => FileHelper::getFileInfos($this->file)]);
    }

    /**
     * If not opened in ajax mode - redirect to the correct page and open modal

     * @return type
     * @throws HttpException
     */
    protected function redirectToModal()
    {
        $url = $this->determineContentFileUrl();
        if ($url === null) {
            throw new HttpException(400, 'Invalid request. Could not find file content url!');
        }

        if ($this->shareSecret) {
            $openUrl = Url::to(['/onlyoffice/open', 'share' => $this->shareSecret]);
        } elseif($this->anchor) {
            $openUrl = Url::to(['/onlyoffice/open', 'guid' => $this->file->guid, 'mode' => $this->mode, 'anchor' => $this->actionData]);
        } else {
            $openUrl = Url::to(['/onlyoffice/open', 'guid' => $this->file->guid, 'mode' => $this->mode]);
        }

        $jsCode = 'var modalOO = humhub.require("ui.modal"); modalOO.get("#onlyoffice-modal").load("' . $openUrl . '");';
        Yii::$app->session->setFlash('executeJavascript', $jsCode);

        return $this->redirect($url);
    }

}
