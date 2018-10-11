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
use craft\web\Controller;
use craft\helpers\UrlHelper;
use inalgiev\webtexttool\Webtexttool;
use inalgiev\webtexttool\models\CoreModel;
use yii\web\Response;

/**
 * Class WebtexttoolController
 * @package inalgiev\webtexttool\controllers
 */
class CoreController extends Controller
{
    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetUrlWithToken()
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $params = array('entryId' => $request->getBodyParam('entryId'), 'locale' => $request->getBodyParam('locale'));

        $status = array('status' => $request->getBodyParam('status'));

        if($status['status'] !== "live") {
            Craft::$app->getTokens()->deleteExpiredTokens();

            $token = Craft::$app->getTokens()->createToken(array('action' => 'entries/viewSharedEntry', 'params' => $params));
            $url = UrlHelper::urlWithToken($request->getBodyParam('url'), $token);
        } else {
            $url = $request->getBodyParam('url');
        }

        if($request->getAcceptsJson()) {
            return $this->asJson(['url' => $url]);
        }
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveContentQualitySettings(): Response {

        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();
        $params = array('entryId' => $request->getBodyParam('entryId'), 'data' => $request->getBodyParam('data'), 'recordId' => $request->getBodyParam('recordId'));

        if($params['entryId'] && $params['recordId']) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($params['entryId']);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $params['entryId'];
        $model->wttContentQualitySettings = $params['data'];

        if(Webtexttool::getInstance()->CoreService->saveRecord($model)) {
            return $this->asJson(['success' => true]);
        };

        return $this->asJson(['success' => false, 'data' => $params['data']]);
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveContentQualitySuggestions(): Response {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();
        $params = array('entryId' => $request->getBodyParam('entryId'), 'data' => $request->getBodyParam('data'), 'recordId' => $request->getBodyParam('recordId'));

        if($params['entryId'] && $params['recordId']) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($params['entryId']);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $params['entryId'];
        $model->wttContentQualitySuggestions = $params['data'];

        if(Webtexttool::getInstance()->CoreService->saveRecord($model)) {
            return $this->asJson(['success' => true]);
        };

        return $this->asJson(['success' => false]);

    }

    /**
     * Intercept the Entry save data
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveRecord()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        if ($id = $request->getBodyParam('entryId')) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($id);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $request->getBodyParam('entryId');
        $model->wttKeywords = $request->getBodyParam('wtt_keyword');
        $model->wttDescription = $request->getBodyParam('wtt_description');
        $model->wttLanguage = $request->getBodyParam('wtt_language');
        $model->wttSynonyms = $request->getBodyParam('wtt_synonym_tags');

        if ($model->validate($model->entryId, true)) {
            Webtexttool::getInstance()->CoreService->saveRecord($model);
        }
    }
}