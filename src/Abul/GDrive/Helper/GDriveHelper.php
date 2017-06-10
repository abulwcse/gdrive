<?php

namespace Abul\GDrive\Helper;

use Abul\GDrive\Exception\ConfigurationMissingException;
use Google_Client;
use Google_Service_Drive;

class GDriveHelper
{
    /**
     * @var Google_Client
     */
    private $_client;

    /**
     * @var ConfigurationHelper
     */
    private $_configHelper;

    /**
     * GDrive constructor.
     *
     * @param bool $initService
     */
    public function __construct($initService = true)
    {
        $this->_client = new Google_Client();
        $this->_client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $this->_client->setAccessType("offline");
        $this->_client->addScope(Google_Service_Drive::DRIVE);
        $this->_configHelper = ConfigurationHelper::getInstance();
        if ($initService) {
            $this->initService();
        }
    }


    /**
     * @throws ConfigurationMissingException
     */
    public function loadAppConfig()
    {
        if (!$this->_configHelper->configExist(ConfigurationHelper::APP_CONFIG_FILE)) {
            throw new ConfigurationMissingException("App configuration missing.");
        }
        $config = json_decode($this->_configHelper->readConfig(ConfigurationHelper::APP_CONFIG_FILE));
        $this->_client->setClientId($config->client_id);
        $this->_client->setClientSecret($config->client_secret);
    }

    /**
     * @throws ConfigurationMissingException
     */
    public function loadAccessConfig()
    {
        if (!$this->_configHelper->configExist(ConfigurationHelper::ACCESS_CONFIG_FILE)) {
            throw new ConfigurationMissingException("Access token missing.");
        }
        $this->_client->setAccessToken($this->_configHelper->readConfig(ConfigurationHelper::ACCESS_CONFIG_FILE));
    }

    /**
     *
     */
    public function initService()
    {
        $this->loadAppConfig();
        $this->loadAccessConfig();
        if($this->_client->isAccessTokenExpired()) {
            $config = $this->_client->fetchAccessTokenWithRefreshToken();
            $this->_configHelper->writeConfig(ConfigurationHelper::ACCESS_CONFIG_FILE, $config);
        }
    }


    /**
     * @return array
     */
    public function getAbout()
    {
        $service = new Google_Service_Drive($this->_client);
        $about = $service->about->get(['fields' => 'user,storageQuota']);
        return [
            'Name' => $about->getUser()->getDisplayName(),
            'Email' => $about->getUser()->getEmailAddress(),
            'Used drive space' => $this->humanReadableFileSize($about->getStorageQuota()->getUsageInDrive()),
            'Used trash space' => $this->humanReadableFileSize($about->getStorageQuota()->getUsageInDriveTrash()),
            'Total space' => $this->humanReadableFileSize($about->getStorageQuota()->getLimit()),
        ];
    }

    /**
     * @return Google_Client
     */
    public function getClient()
    {
        return $this->_client;
    }


    /**
     * @param $query
     * @return \Google_Service_Drive_FileList
     */
    public function getAllFiles($query)
    {
        $service = new Google_Service_Drive($this->_client);
        return $service->files->listFiles(['q' => $query]);
    }

    /**
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    private function humanReadableFileSize($bytes, $decimals = 2)
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}