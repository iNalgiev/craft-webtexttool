<?php

namespace inalgiev\webtexttool\models;

use craft\base\Model;

/**
 * Webtexttool User Model
 *
 * @inheritdoc
 */
class UserModel extends Model
{
    /**
     * @var integer The current logged in user id
     */
    public $userId;

    /**
     * @var string The access token generated by webtexttool API service
     */
    public $accessToken;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['userId', 'integer'],
            ['accessToken', 'string']
        ];
    }
}
