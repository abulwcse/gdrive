<?php

namespace Abul\GDrive\Tests\Helper;

use PHPUnit_Framework_TestCase;
use Abul\GDrive\Helper\ConfigurationHelper;

class ConfigurationHelperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if(!file_exists(getenv('HOME') . '/.gdrive')) {
            mkdir(getenv('HOME') . '/.gdrive');
        }
    }
    public function testGetInstance()
    {
        $this->assertInstanceOf('Abul\GDrive\Helper\ConfigurationHelper', ConfigurationHelper::getInstance());
    }

    public function testGetConfigDir()
    {
        $this->assertEquals(getenv('HOME') . '/.gdrive', ConfigurationHelper::getInstance()->getConfigDirectory());
    }

    public function testConfigExists()
    {
        $this->assertEquals(
          file_exists(getenv('HOME') . '/.gdrive/app.json'),
          ConfigurationHelper::getInstance()->configExist(ConfigurationHelper::APP_CONFIG_FILE)
        );
    }

    public function testConfigExists2()
    {
        $appConfigFile = getenv('HOME') . '/.gdrive/app.json';
        if (!file_exists($appConfigFile)) {
            touch($appConfigFile);
        }
        $this->assertTrue(
            ConfigurationHelper::getInstance()->configExist(ConfigurationHelper::APP_CONFIG_FILE)
        );
        unlink($appConfigFile);

        $this->assertFalse(
            ConfigurationHelper::getInstance()->configExist(ConfigurationHelper::APP_CONFIG_FILE)
        );
    }

    public function testReadConfig()
    {
        $appConfigFile = getenv('HOME') . '/.gdrive/app.json';
        if (file_exists($appConfigFile)) {
            unlink($appConfigFile);
        }
        file_put_contents($appConfigFile, 'foo');

        $this->assertEquals('foo', ConfigurationHelper::getInstance()->readConfig(ConfigurationHelper::APP_CONFIG_FILE));
    }

    public function testWriteReadConfig()
    {
        $appConfigFile = getenv('HOME') . '/.gdrive/app.json';
        if (!file_exists($appConfigFile)) {
            touch($appConfigFile);
        }
        ConfigurationHelper::getInstance()->writeConfig(ConfigurationHelper::APP_CONFIG_FILE, ['foo' => 'bar']);
        $this->assertEquals(
            '{"foo":"bar"}', ConfigurationHelper::getInstance()->readConfig(ConfigurationHelper::APP_CONFIG_FILE));
    }

    public static function tearDownAfterClass()
    {
        $appConfigFile = getenv('HOME') . '/.gdrive/app.json';
        if (file_exists($appConfigFile)) {
            unlink($appConfigFile);
        }
        $accessConfigFile = getenv('HOME') . '/.gdrive/access.json';
        if (file_exists($accessConfigFile)) {
            unlink($accessConfigFile);
        }
        rmdir(getenv('HOME') . '/.gdrive');
    }
}
