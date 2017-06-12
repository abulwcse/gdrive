<?php

namespace Abul\GDrive\Tests\Helper;

use Abul\GDrive\Helper\GDriveHelper;
use PHPUnit_Framework_TestCase;

class GDriverHelperTest extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $helper = $this->getMockBuilder('Abul\GDrive\Helper\GDriveHelper')
            ->setMethods(['loadAppConfig', 'loadAccessConfig', 'refreshAccessTokenIfNeeded'])
            ->getMock();

        $this->assertEmpty($helper->initService());
    }

    public function testLoadAppConfig()
    {
        $configHelper = $this->getMockBuilder('Abul\GDrive\Helper\ConfigurationHelper')
            ->setMethods(['getConfigDirectory'])
            ->getMock();

        $configHelper->expects($this->any())
            ->method('getConfigDirectory')
            ->willReturn(__DIR__);

        $helper = new GDriveHelper(false);

        $helper->setConfigHelper($configHelper);

        $this->assertInstanceOf(get_class($configHelper), $helper->getConfigHelper());

        touch($configHelper->getConfigDirectory() . '/app.json');
        $appConfig = ['client_id' => 'client_id','client_secret' => 'client_secret'];
        file_put_contents($configHelper->getConfigDirectory() . '/app.json', json_encode($appConfig));
        $helper->loadAppConfig();
        $this->assertEquals('client_id', $helper->getClient()->getClientId());
        $this->assertEquals('client_secret', $helper->getClient()->getClientSecret());
    }

    public static function tearDownAfterClass()
    {
        $appConfigFile = __DIR__ . '/app.json';
        if (file_exists($appConfigFile)) {
            unlink($appConfigFile);
        }
        $accessConfigFile = __DIR__ . '/access.json';
        if (file_exists($accessConfigFile)) {
            unlink($accessConfigFile);
        }
    }

}