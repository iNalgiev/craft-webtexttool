<?php
/**
 * Webtexttool plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.webtexttool.com/
 * @copyright Copyright (c) 2018 Israpil Nalgiev
 */

namespace inalgiev\webtexttool\controllers;

use Craft;
use inalgiev\webtexttool\Webtexttool;
use craft\web\Controller;
use inalgiev\webtexttool\models\UserModel;
use yii\web\Response;


/**
 * Class WebtexttoolController
 * @package inalgiev\webtexttool\controllers
 */
class UserController extends Controller
{
    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveAccessToken(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $model = new UserModel();
        $model->userId = $request->getParam('userId');
        $model->accessToken = $request->getParam('accessToken');

        if($request->getAcceptsJson()){
            Webtexttool::getInstance()->UserService->saveAccessToken($model);

            return $this->asJson(['success' => true]);
        }

        return $this->asJson(['success' => false]);
    }
}