<?php
/**
 * Created by PhpStorm.
 * User: abulh
 * Date: 10/06/2017
 * Time: 1:32
 */

namespace Abul\GDrive\Helper;


class ConfigurationHelper
{
    const ACCESS_CONFIG_FILE = 'access.json';

    const APP_CONFIG_FILE = 'app.json';

    /**
     * @var $this
     */
    private static $instance;

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            $instance = new static();
            static::$instance = $instance;
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function getConfigDirectory()
    {
        return getenv('HOME') . '/.gdrive';
    }

    /**
     * @param $configType
     * @return bool
     */
    public function configExist($configType)
    {
        if (file_exists($this->getConfigDirectory() . '/' . $configType)) {
            return true;
        }
        return false;
    }

    /**
     * @param $configType
     * @param array $config
     *
     * @return bool|int
     */
    public function writeConfig($configType, array $config)
    {
        if (!(file_exists($this->getConfigDirectory()) && is_dir($this->getConfigDirectory()))) {
            mkdir($this->getConfigDirectory());
        }
        return file_put_contents($this->getConfigDirectory() . "/" . $configType, json_encode($config));
    }

    /**
     * @param $configType
     *
     * @return string
     */
    public function readConfig($configType)
    {
        $json = "{}";
        if (file_exists($this->getConfigDirectory() . '/' . $configType)) {
            $json = file_get_contents($this->getConfigDirectory() . "/" . $configType);
        }
        return $json;
    }
}
