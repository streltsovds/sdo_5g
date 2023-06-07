<?php

/**
 * Класс Resource_GenerateController обеспечивает генерацию ресурсов на основе медиа-файлов клиента.
 * По договорённости эти файлы должны лежать в папке "data\upload\resource\generate"
 */
class Resource_GenerateController extends HM_Controller_Action
{
    /**
     * Массив всех ресурсов, хранящихся в БД, представленный в виде пары resource_id => filename
     * @var array
     */
    private $existingResources;

    /**
     *  Путь к папке "generate" на сервере
     * @var string
     */
    private $generateDirectory;

    /**
     * Массив с id свежесгенерированных ресурсов
     * @var array
     */
    private $generatedResources;

    /**
     * Массив с id ресурсов, для которых файлы бвли скопированы из папки "generate" в папку "resource"
     * @var array
     */
    private $refreshedResources;

    /**
     *  Инициализируем свойства контроллера начальными данными
     */
    public function init()
    {
        $this->existingResources = $this->getService('Resource')->fetchAll(array('type = ?' => HM_Resource_ResourceModel::TYPE_EXTERNAL))->getList('resource_id', 'filename');
        $this->generateDirectory = realpath(Zend_Registry::get('config')->path->upload->resource . DIRECTORY_SEPARATOR . 'generate');

        parent::init();
    }

    /**
     * Экшн для генерации ресурсов на основе файлов из каталога "generate"
     */
    public function indexAction()
    {
        $resourcesFiles = array();
        foreach (scandir($this->generateDirectory) as $item) {
            $convertedItem = iconv("windows-1251", "UTF-8", $item);
            if (!is_dir($this->generateDirectory . DIRECTORY_SEPARATOR . $item) && $item != '.gitignore') $resourcesFiles[$item] = $convertedItem;
        }

        foreach ($resourcesFiles as $file => $convertedFile) {
            in_array($convertedFile, $this->existingResources) ?
                $this->copyAndRenameResourceFile($file, $convertedFile) :
                $this->createResourceBasedOnFile($file, $convertedFile);
        }

        $generatedResourcesCount = count($this->generatedResources);
        $refreshedResourcesCount = count($this->refreshedResources) - $generatedResourcesCount;
        $message = _("Было сгенерировано ресурсов: {$generatedResourcesCount} и обновлено ресурсов: {$refreshedResourcesCount}.");
        $this->_flashMessenger->addMessage($message);
        $this->_redirector->gotoSimple('index', 'list', 'resource');
    }

    /**
     * Копируется файл из папки "generate" в папку "resource" этажом выше.
     *
     * @param $file - имя файла
     * @param $convertedFile - имя файла в UTF-8
     */
    private function copyAndRenameResourceFile($file, $convertedFile)
    {
        $resourceId  = array_search($convertedFile, $this->existingResources);
        $sourcePath  = $this->generateDirectory . DIRECTORY_SEPARATOR . $file;
        $destination = $this->generateDirectory . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . $resourceId;
        copy($sourcePath, $destination);
        $this->refreshedResources[] = $resourceId;
    }

    /**
     * На основе файла создаётся ресурс, после чего файл из папки "generate" копируется в папку "resource"
     *
     * @param $file - имя файла
     * @param $convertedFile - имя файла в UTF-8
     */
    private function createResourceBasedOnFile($file, $convertedFile)
    {
        $sourcePath = $this->generateDirectory . DIRECTORY_SEPARATOR . $file;
        $data = array(
            'title'      => $convertedFile,
            'filename'   => $convertedFile,
            'type'       => HM_Resource_ResourceModel::TYPE_EXTERNAL,
            'filetype'   => HM_Files_Videoblock_VideoblockModel::getFileType($convertedFile),
            'created'    => date('Y-m-d H:i:s'),
            'updated'    => date('Y-m-d H:i:s'),
            'created_by' => $this->getService('User')->getCurrentUserId(),
            'status'     => HM_Resource_ResourceModel::STATUS_PUBLISHED,
            'location'   => HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL
        );
        if (is_readable($sourcePath))
            $data['volume'] = $this->formatBytes(filesize($sourcePath));

        $newResource = $this->getService('Resource')->insert($data);
        $this->existingResources[$newResource->resource_id] = $convertedFile;
        $this->copyAndRenameResourceFile($file, $convertedFile);
        $this->generatedResources[] = $newResource->resource_id;
    }

    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'kB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . $units[$pow];
    }
}