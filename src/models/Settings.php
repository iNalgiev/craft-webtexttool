<?php
/**
 * Textmetrics plugin for Craft CMS
 *
 * @author    Israpil Nalgiev
 * @link      https://www.textmetrics.com/
 * @copyright Copyright (c) 2019 Textmetrics
 */
namespace inalgiev\webtexttool\models;

use craft\base\Model;

/**
 * Class Settings
 * @since 1.0.0
 */
class Settings extends Model
{
    /** @var string|null $wttApiKey The API key */
    public $wttApiKey;
}