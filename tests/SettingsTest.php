<?php

namespace solutosoft\settings\tests;

use solutosoft\settings\Settings;
use Yii;
use yii\db\Query;

class SettingsTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mockApplication();
    }

    public function testStorage()
    {
        $settings = new Settings();

        $settings->set('key1', 999);
        $this->assertEquals(999, $settings->get('key1'));

        $settings->set('key2', 'test');
        $this->assertEquals('test', $settings->get('key2'));

        $settings->set('key3', false);
        $this->assertEquals(0, $settings->get('key3'));

        $this->assertEquals('default', $settings->get('key-default', 'default'));

        $this->assertCount(3, $settings->all());

        $settings->save(['key3' => 'value3', 'key4' => 'value4']);
        $this->assertCount(4, $settings->all());

        $settings->remove('key1');
        $this->assertCount(3, $settings->all());

        $settings->removeAll();
        $this->assertEmpty($settings->all());
    }

    public function testEvents()
    {
        $settings = new Settings([
            'on beforeExecute' => function ($event) {
                $event->data = ['user_id' => 1];
            }
        ]);

        $settings->set('website', 'http://example.org');
        $this->assertEquals('http://example.org', $settings->get('website'));

        Yii::$app->db->createCommand()->insert('setting', [
            'key' => 'website',
            'value' => 'http://test.org',
            'user_id' => 2
        ])->execute();

        $values = $settings->all();
        $this->assertCount(1, $values);
        $this->assertEquals(['website' => 'http://example.org'], $values);
    }
}
