<?php

namespace Soluto\Settings;

use yii\base\Event;

/**
 * SettingsEvent represents the parameter needed by [[Settings]] events.
 */
class SettingsEvent extends Event
{
    /**
     * @param array $columns the column data (name => value) to be updated.
     */
    public $columns = [];

}
