<?php

namespace Soluto\Settings;

use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\db\Query;


class Settings extends Component
{
    /**
     * @event ModelEvent an event that is triggered before inserting a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the insertion.
     */
    const EVENT_BEFORE_FIND = 'beforeFind';
    /**
     * @event ModelEvent an event that is triggered before inserting a record.
     * You may set [[ModelEvent::isValid]] to be `false` to stop the insertion.
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @var string Name of the table where configurations will be stored
     */
    public $tableName = '{{%settings}}';

    /**
     * @var string Name of column where keys will be stored
     */
    public $keyColumnName = 'key';

    /**
     * @var string Name of column where values will be stored
     */
    public $valueColumnName = 'value';

    /**
     * @return Connection the DB connection instance
     */
    protected function getDb()
    {
        return Yii::$app->getDb();
    }

    /**
     * Whether the configuration exists in the database
     * @param string $name configuration name
     * @param integer $tenantId The tenant id value
     * @return bool
     */
    protected function exists($name)
    {
        $query = $this->createQuery($name);
        return $query->exists();
    }

    /**
     * Returns configuration value from database
     * @param string $name configuration name
     * @param mixed $defaultValue the default value to be returned when the session variable does not exist.
     * @return string value stored in database
     */
    public function get($name, $defaultValue = null)
    {
        $value = null;
        $query = $this->createQuery($name);
        $row = $query->one($this->getDb());

        $value = ($row) ? $row[$this->valueColumnName] : null;

        if (is_string($value) && trim($value) == '') {
            $value = null;
        }

        return ($value !== null) ? $value : $defaultValue;
    }

    /**
     * Store configuration value to database
     * @param string $name
     * @param mixed $value
     * @param integer $tenantId The tenant id value
     */
    public function set($name, $value, $tenantId = null)
    {
        $db = $this->getDb();

        $event = new SaveEvent();
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);

        $columns = array_merge($event->columns, [$this->valueColumnName => $value]);
        $where = array_merge($event->columns, [$this->keyColumnName => $name]);

        if ($this->exists($name)) {
            $db->createCommand()
                ->update($this->tableName, $columns, $where)
                ->execute();
        } else  {
            $values = array_merge([$this->keyColumnName => $name], $columns);

            $db->createCommand()
                ->insert($this->tableName, $values)
                ->execute();
        }
    }

    /**
     * Retrieves all configurations stored in database
     * @param integer $tenantId
     * @return array
     */
    public function all()
    {
        $result = [];

        $query = $this->createQuery()
            ->addSelect($this->keyColumnName);

        $rows = $query->all($this->getDb());

        foreach ($rows as $row) {
            $result[$row[$this->keyColumnName]] = $row[$this->valueColumnName];
        }

        return $result;
    }

    /**
     * Store all configuration in database
     * @param array $names
     */
    public function save($names)
    {
        foreach ($names as $key => $value) {
            $this->set($key, $value) ;
        }
    }

    /**
     * Deletes specified configurations (or all if none specified) from the parameters table
     * @param array|string $names
     * @param integer $tenantId
     */
    public function delete($names = [])
    {
        if (is_array($names)) {
            $where = ['IN', $this->keyColumnName, $names];
        } else {
            $where = [$this->keyColumnName => $names];
        }

        $event = new ConditionEvent();
        $this->trigger(self::EVENT_BEFORE_FIND, $event);

        if ($event->condition) {
            $where = array_merge($event->condition, $where);
        }

        $this->getDb()
            ->createCommand()
            ->delete($this->tableName, $where)
            ->execute();
    }

    /**
     * Creates query to find settings value
     * @param string $name
     * @return \yii\db\Query
     */
    protected function createQuery($name = null)
    {
        $query = (new Query())
            ->select([$this->valueColumnName])
            ->from($this->tableName);

        $event = new ConditionEvent();
        $this->trigger(self::EVENT_BEFORE_FIND, $event);

        if ($event->condition) {
            $query->andWhere($event->condition);
        }

        if ($name) {
            $query->andWhere([$this->keyColumnName => $name]);
        }

        return $query;
    }

}
