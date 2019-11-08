<?php

namespace solutosoft\settings\Tests;

use Yii;
use yii\base\NotSupportedException;
use yii\base\UnknownMethodException;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\test\FixtureTrait;
use yii\test\BaseActiveFixture;

/**
 * This is the base class for all unit tests.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    use FixtureTrait;

    /**
     * This method is called before the first test of this test class is run.
     * Attempts to load vendor autoloader.
     * @throws \yii\base\NotSupportedException
     */
    public static function setUpBeforeClass()
    {
        $vendorDir = __DIR__ . '/../vendor';
        $vendorAutoload = $vendorDir . '/autoload.php';
        if (file_exists($vendorAutoload)) {
            require_once($vendorAutoload);
        } else {
            throw new NotSupportedException("Vendor autoload file '{$vendorAutoload}' is missing.");
        }
        require_once($vendorDir . '/yiisoft/yii2/Yii.php');
        Yii::setAlias('@vendor', $vendorDir);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->unloadFixtures();
        $this->destroyApplication();
        parent::tearDown();
    }

    /**
     * Destroys the application instance created by [[mockApplication]].
     */
    protected function destroyApplication()
    {
        if (\Yii::$app) {
            if (\Yii::$app->has('session', true)) {
                \Yii::$app->session->close();
            }
            if (\Yii::$app->has('db', true)) {
                Yii::$app->db->close();
            }
        }
        Yii::$app = null;
        Yii::$container = new Container();
    }

    /**
     * Populates Yii::$app with a new application
     * @param array $extra
     * @return void
     */
    protected function mockApplication($extra = [])
    {
        $config  = ArrayHelper::merge([
            'class' => 'yii\console\Application',
            'id' => 'app-test',
            'basePath' => dirname(__DIR__) . '/../',
            'vendorPath' => __DIR__ . '/../../vendor',
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ], $extra);

        Yii::createObject($config);

        $this->setupDatabase();
    }

    protected function setupDatabase()
    {
        $db = Yii::$app->getDb();

        $db->createCommand()->createTable('setting', [
            'key' => 'key',
            'value' => 'text',
            'user_id' => 'integer'
        ])->execute();
    }

}
