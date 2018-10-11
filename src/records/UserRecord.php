<?php

namespace inalgiev\webtexttool\records;

use craft\db\ActiveRecord;

/**
 * Webtexttool user record
 *
 * @inheritdoc
 */
class UserRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%webtexttool_user}}';
    }
}
