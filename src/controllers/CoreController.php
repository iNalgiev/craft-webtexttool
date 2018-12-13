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
use yii\base\Exception;

/**
 * Class WebtexttoolController
 * @package inalgiev\webtexttool\controllers
 */
class CoreController extends Controller
{
    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     * @throws Exception
     */
    public function actionGetUrlWithToken()
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $params = ['entryId' => $request->getBodyParam('entryId')];
        $status = ['status' => $request->getBodyParam('status')];
        $entryUrl = $request->getBodyParam('url');

        if($status['status'] !== "live") {
            Craft::$app->getTokens()->deleteExpiredTokens();

            $token = Craft::$app->getTokens()->createToken([
                'entries/view-shared-entry',
                $params
            ]);

            if ($token === false) {
                throw new Exception('There was a problem generating the token.');
            }

            $url = UrlHelper::urlWithToken($entryUrl, $token);
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
     * @param $attr array The base class Event.
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveRecord($attr)
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $entryId = $attr->sender->attributes['id'] ? $attr->sender->attributes['id'] : $request->getBodyParam('entryId');

        if ($entryId) {
            $model = Webtexttool::getInstance()->CoreService->getCoreData($entryId);
        } else {
            $model = new CoreModel();
        }

        $model->entryId = $entryId;
        $model->wttKeywords = $request->getBodyParam('wtt_keyword');
        $model->wttDescription = $request->getBodyParam('wtt_description');
        $model->wttLanguage = $request->getBodyParam('wtt_language');
        $model->wttSynonyms = $request->getBodyParam('wtt_synonym_tags');

        if ($model->validate($model->entryId, true)) {
            Webtexttool::getInstance()->CoreService->saveRecord($model);
        }
    }
}