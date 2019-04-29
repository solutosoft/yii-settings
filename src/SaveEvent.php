<?php

namespace Soluto\Settings;

/**
 * SaveEvent represents the parameter needed by [[Settings]] save events.
 */
class SaveEvent extends ConditionEvent
{
    /**
     * @param array $columns the column data (name => value) to be updated.
     */
    public $columns = [];

}
