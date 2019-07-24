<?php
/**
 * Textmetrics plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.textmetrics.com/
 * @copyright Copyright (c) 2019 Textmetrics
 */

namespace inalgiev\webtexttool\services;

use inalgiev\webtexttool\models\UserModel;
use inalgiev\webtexttool\records\UserRecord;
use yii\base\Component;
use craft\db\Query;

/**
 * Class WebtexttoolService
 * @package inalgiev\webtexttool\services
 */
class UserService extends Component
{
    /**
     * Returns the webtexttool_user table data.
     *
     * @param int $userId
     * @return array|bool
     */
    public function getUserData(int $userId)
    {
        $dataArray = (new Query())
            ->from(['{{%webtexttool_user}}'])
            ->select(['id', 'userId', 'accessToken'])
            ->where(['userId' => $userId])
            ->one();

        return $dataArray;
    }

    /**
     * Save a new or an existing access token to the database.
     *
     * @param UserModel $userModel
     * @return bool
     */
    public function saveAccessToken(UserModel $userModel)
    {
        // Make sure it validates
        if ($userModel->validate(null, true)) {
            // Save it out to a record
            $userModelRecord = UserRecord::findOne([
                'userId' => $userModel->userId,
            ]);

            if (!$userModelRecord) {
                $userModelRecord = new UserRecord();
            }

            $userModelRecord->setAttributes($userModel->getAttributes(), false);

            if ($userModelRecord->save()) {
                return true;
            }
        } else {
            return false;
        }
    }
}