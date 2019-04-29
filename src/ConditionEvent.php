<?php

namespace Soluto\Settings;

use yii\base\Event;

/**
 * ConditionEvent represents the parameter needed by [[Settings]] find and remove events.
 */
class ConditionEvent extends Event
{
    /**
     * @var string|array $condition the condition that will be put in the WHERE part. Please
     * refer to [[\yii\db\Query::where()]] on how to specify condition.
     */
    public $condition;
}
