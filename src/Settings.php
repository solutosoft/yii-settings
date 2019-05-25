<?php

namespace Soluto\Settings;

use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\db\Query;
use yii\base\Event;

class Settings extends Component
{
    /**
     * @event SettingsEvent an event that is triggered before execute command.
     */
    const EVENT_BEFORE_EXECUTE = 'beforeExecute';

    /*
     * @var array The settings cache
     */
    private $_data = [];

    /**
     * @var string Name of the table where configurations will be stored
     */
    public $tableName = '{{%setting}}';

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
     * Whether the setting exists in the database
     * @param string $name the setting name
     * @return bool
     */
    protected function exists($name)
    {
        if (isset($this->_data[$name])) {
            return true;
        }

        $query = $this->createQuery($name);
        return $query->exists();
    }

    /**
     * Returns setting value from database
     * @param string $name setting name
     * @return mixed $defaultValue
     */
    public function get($name, $defaultValue = null)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        $value = null;
        $query = $this->createQuery($name);
        $row = $query->one($this->getDb());

        $value = ($row) ? $row[$this->valueColumnName] : null;

        if (is_string($value) && trim($value) == '') {
            $value = null;
        }

        if ($value === null) {
            $value = $defaultValue;
        }

        $this->_data[$name] = $value;
        return $value;
    }

    /**
     * Store setting value to database
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $db = $this->getDb();
        $values = [$this->valueColumnName => $value];
        $where = [$this->keyColumnName => $name];

        $event = $this->beforeExecute();
        if ($event->data) {
            $values = array_merge($event->data, $values);
            $where = array_merge($event->data, $where);
        }

        if ($this->exists($name)) {
            $db->createCommand()
                ->update($this->tableName, $values, $where)
                ->execute();
        } else  {
            $values = array_merge($values, $where);

            $db->createCommand()
                ->insert($this->tableName, $values)
                ->execute();
        }

        $this->_data[$name] = $value;
    }

    /**
     * Retrieves all setting stored in database
     * @return array
     */
    public function all()
    {
        $result = [];

        $query = $this->createQuery()
            ->addSelect($this->keyColumnName);

        $rows = $query->all($this->getDb());

        foreach ($rows as $row) {
            $value = $row[$this->valueColumnName];
            $name = $row[$this->keyColumnName];

            $result[$name] = $value;
            $this->_data[$name] = $value;
        }

        return $result;
    }

    /**
     * Store all settings in database
     * @param array $names
     */
    public function save($names)
    {
        foreach ($names as $key => $value) {
            $this->set($key, $value) ;
        }
    }

    /**
     * Remove specified setting
     * @param array|string $name
     */
    public function remove($name)
    {
        $where = [$this->keyColumnName => $name];

        $event = $this->beforeExecute();
        if ($event->data) {
            $where = array_merge($event->data, $where);
        }

        $this->getDb()
            ->createCommand()
            ->delete($this->tableName, $where)
            ->execute();

        unset($this->_data[$name]);
    }

    /**
     * Removes all settings
     */
    public function removeAll()
    {
        $event = $this->beforeExecute();
        $where = $event->data ? $event->data : '';

        $this->getDb()
            ->createCommand()
            ->delete($this->tableName, $where)
            ->execute();

        $this->_data[] = [];
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

        $event = $this->beforeExecute();

        if ($event->data) {
            $query->andWhere($event->data);
        }

        if ($name) {
            $query->andWhere([$this->keyColumnName => $name]);
        }

        return $query;
    }

    /**
     * This method is called at the before execute db command
     * @return yii\base\Event
     */
    protected function beforeExecute()
    {
        $event = new Event();
        $this->trigger(self::EVENT_BEFORE_EXECUTE, $event);
        return $event;
    }

}
