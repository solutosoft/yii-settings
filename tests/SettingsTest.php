<?php

namespace Soluto\Settings\Tests;

use Soluto\Settings\Settings;

class SettingsTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->mockApplication();
    }

    public function testStorage()
    {
        $settings = new Settings();

        $settings->set('key1', 1);
        $this->assertEquals(1, $settings->get('key1'));

        $settings->set('key1', 'test');
        $this->assertEquals('test', $settings->get('key1'));

        $this->assertCount(1, $settings->all());

        $settings->save(['key3' => 'value3', 'key4' => 'value4']);
        $this->assertCount(3, $settings->all());

        $settings->delete('key1');
        $this->assertCount(2, $settings->all());

        $settings->delete(['key2', 'key3', 'key4']);
        $this->assertEmpty($settings->all());
    }
}
