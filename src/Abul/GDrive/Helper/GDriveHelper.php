<?php

namespace Abul\GDrive\Helper;

use Abul\GDrive\Exception\ConfigurationMissingException;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GDriveHelper
{
    /**
     * @var Google_Client
     */
    private $_client;

    /**
     * @var Google_Service_Drive
     */
    private $_service;

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
     * Init the google drive service
     */
    public function initService()
    {
        $this->loadAppConfig();
        $this->loadAccessConfig();
        if ($this->_client->isAccessTokenExpired()) {
            $config = $this->_client->fetchAccessTokenWithRefreshToken();
            $this->_configHelper->writeConfig(ConfigurationHelper::ACCESS_CONFIG_FILE, $config);
        }
        $this->_service = new Google_Service_Drive($this->_client);
    }

    /**
     * Load Application config
     *
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
     * Load Access config
     *
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
     * Get Basic details about the drive and user logged in
     *
     * @return array
     */
    public function getAbout()
    {
        $about = $this->_service->about->get(['fields' => 'user,storageQuota']);
        return [
            'Name' => $about->getUser()->getDisplayName(),
            'Email' => $about->getUser()->getEmailAddress(),
            'Used drive space' => StringHelper::humanReadableFileSize($about->getStorageQuota()->getUsageInDrive()),
            'Used trash space' => StringHelper::humanReadableFileSize($about->getStorageQuota()->getUsageInDriveTrash()),
            'Total space' => StringHelper::humanReadableFileSize($about->getStorageQuota()->getLimit()),
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
     * Upload a file
     *
     * @param string $file
     * @return Google_Service_Drive_DriveFile
     */
    public function uploadFile($file)
    {
        $diveFile = new Google_Service_Drive_DriveFile();
        $diveFile->setName(basename($file));
        $diveFile->setMimeType(mime_content_type($file));
        $data = file_get_contents($file);
        $createdFile = $this->_service->files->create($diveFile, array(
            'data' => $data,
            'mimeType' => $diveFile->getMimeType(),
            'uploadType' => 'multipart'
        ));
        return $createdFile;
    }

    /**
     * Download a file to current directory
     *
     * @param $fileId
     */
    public function downloadFileDetail($fileId)
    {
        $file = $this->getFileDetail($fileId);
        $content = $this->_service->files->get($fileId, ["alt" => "media"]);
        $outHandle = fopen($file->getName(), "w+");
        while (!$content->getBody()->eof()) {
            fwrite($outHandle, $content->getBody()->read(1024));
        }
        fclose($outHandle);
    }

    /**
     * Get details about a file from google drive
     *
     * @param string $fileId
     * @return Google_Service_Drive_DriveFile
     */
    public function getFileDetail($fileId)
    {
        return $this->_service->files->get($fileId, [
            'fields' => '*'
        ]);
    }

    /**
     * Search for a file in google drive based on the query
     *
     * @param $query
     * @return \Google_Service_Drive_FileList
     */
    public function getAllFiles($query, $orderBy = null)
    {
        $searchParms['q'] = $query;
        if (!empty($orderBy)) {
            $searchParms['orderBy'] = $orderBy;
        }
        return $this->_service->files->listFiles($searchParms);
    }
}