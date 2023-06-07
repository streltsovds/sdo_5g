<?php

include_once 'ElFinderVolumeHmLocalFileSystem.php';

/**
 *  Обёртка над elFinder v.2.1 для переопределения методов реализациями, написанными для первой версии elFinder.
 *
 */
class HM_ElFinder extends elFinder
{
    const UNPLUG_STORAGE_MODEL = 0; // set to 1 for use only parent elFinder methods without linking Storage model and logic

    private $subjectName;
    private $subjectId;
    private $isModerator;
    private $fileInfo;
    /** @var HM_Storage_StorageService storageService */
    private $storageService;
    private $activityUsersCache;
    private $usersFolders;
    private $rootTitle;
    private $rootHash;

    /**
     * Documentation for connector options:
     * https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
     * object options
     *
     * @var array
     **/
    private $options = [
        'disabled'     => [
            'duplicate',
            'read',
            'edit',
            'archive',
            'extract'
        ],      // list of not allowed commands
        'dirSize'      => true,         // count total directories sizes
        'URL'          => '/',           // root directory URL
        'dateFormat'   => 'j M Y H:i',  // file modification date format
        'mimeDetect'   => 'auto',       // files mimetypes detection method (finfo, mime_content_type, linux (file -ib), bsd (file -Ib), internal (by extensions))
        'uploadAllow'  => ['all'],      // mimetypes which allowed to upload
        'uploadDeny'   => [
            'application/xml',
            'application/javascript',
            'application/octet-stream',
            'application/x-dosexec',
            'text/x-php',
            'text/html',
            'text/javascript',
            'text/xml',
//            'unknown', // ВАЖНО! Раскомментировать для production

        ], // mimetypes which not allowed to upload
        'uploadOrder'  => 'allow,deny', // order to process uploadAllow and uploadAllow options
        'fileURL'      => true,         // display file URL in "get info"
        'ignoreDotFiles' => true,       // do not display dot files
    ];

    /**
     * extensions/mimetypes for mimetypeDetect = 'internal'
     *
     * @var array
     **/
    private $mimetypes = [
        //applications
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'exe'   => 'application/octet-stream',
        'doc'   => 'application/vnd.ms-word',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'pptx'  => 'application/vnd.ms-powerpoint',
        'pps'   => 'application/vnd.ms-powerpoint',
        'pdf'   => 'application/pdf',
        'xml'   => 'application/xml',
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'swf'   => 'application/x-shockwave-flash',
        // archives
        'gz'    => 'application/x-gzip',
        'tgz'   => 'application/x-gzip',
        'bz'    => 'application/x-bzip2',
        'bz2'   => 'application/x-bzip2',
        'tbz'   => 'application/x-bzip2',
        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar',
        'tar'   => 'application/x-tar',
        '7z'    => 'application/x-7z-compressed',
        // texts
        'txt'   => 'text/plain',
        'php'   => 'text/x-php',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'js'    => 'text/javascript',
        'css'   => 'text/css',
        'rtf'   => 'text/rtf',
        'rtfd'  => 'text/rtfd',
        'py'    => 'text/x-python',
        'java'  => 'text/x-java-source',
        'rb'    => 'text/x-ruby',
        'sh'    => 'text/x-shellscript',
        'pl'    => 'text/x-perl',
        'sql'   => 'text/x-sql',
        // images
        'bmp'   => 'image/x-ms-bmp',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'png'   => 'image/png',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'tga'   => 'image/x-targa',
        'psd'   => 'image/vnd.adobe.photoshop',
        //audio
        'mp3'   => 'audio/mpeg',
        'mid'   => 'audio/midi',
        'ogg'   => 'audio/ogg',
        'mp4a'  => 'audio/mp4',
        'wav'   => 'audio/wav',
        'wma'   => 'audio/x-ms-wma',
        // video
        'avi'   => 'video/x-msvideo',
        'dv'    => 'video/x-dv',
        'mp4'   => 'video/mp4',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mov'   => 'video/quicktime',
        'wm'    => 'video/x-ms-wmv',
        'flv'   => 'video/x-flv',
        'mkv'   => 'video/x-matroska'
    ];

    /**
     * Command result to send to client
     *
     * @var array
     **/
    private $result = [];

    public function __construct($subjectName, $subjectId, $isModerator)
    {
        $config    = Zend_Registry::get('config');
        
        $this->subjectName    = $subjectName;
        $this->subjectId      = $subjectId;
        $this->isModerator    = $isModerator;
        $this->storageService = $this->getService('Storage');
        $this->rootHash       = $config->elFinder->root_hash;
        $this->rootTitle      = $config->elFinder->root_title;
        $this->usersFolders   = $config->elFinder->users_folders;

        if($this->getService('User')->isEndUser()) {
            $userId    = $this->getService('User')->getCurrentUserId();
            $urlAdd = $this->usersFolders; //. '/' . $userId;
        } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_LESSONS == $this->subjectName) {
            $urlAdd = $this->storageService->getSubjectLessonsPath($this->subjectId);
        } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS == $this->subjectName) {
            $urlAdd = $this->storageService->getSubjectExtrasPath($this->subjectId);
        } else {
            $urlAdd = '';
        }

        $filesPath = APPLICATION_PATH .'/../public/' . $config->src->upload->files;
        $filesUrl  = dirname($_SERVER['PHP_SELF']) . '/public/' . $config->src->upload->files;
        $rootPath  = $filesPath . $urlAdd;

        $url = $filesUrl . $urlAdd;

        if(!function_exists('access')) {
            /**
             * This method will disable accessing files/folders starting from '.' (dot)
             *
             * @param  string    $attr    attribute name (read|write|locked|hidden)
             * @param  string    $path    absolute file path
             * @param  string    $data    value of volume option `accessControlData`
             * @param  object    $volume  elFinder volume driver object
             * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
             * @param  string    $relpath file path relative to volume root directory started with directory separator
             * @return bool|null
             **/
            function access($attr, $path, $data, $volume, $isDir, $relpath) {
                $basename = basename($path);
                return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
                && strlen($relpath) !== 1                    // but with out volume root
                    ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
                    :  null;                                 // else elFinder decide it itself
            }
        }

        if(!function_exists('reload')) {
            /**
             * Simple callback catcher
             *
             * @param string $cmd command name
             * @param array $result command result
             * @param array $args command arguments from client
             * @param object $elfinder elFinder instance
             * @return void|true
             **/
            function reload($cmd, $result, $args, $elfinder)
            {
                return true;
            }
        }

        // Почему не $this->options ?
        $opts = [
            // 'debug' => true,
            'bind'  => [
                'edit extract mkdir mkfile rm duplicate paste upload put rename paste' => ['reload'],
            ],
            'roots' => [
                // Items volume
                [
                    'driver'        => 'HmLocalFileSystem',         // driver for accessing file system (REQUIRED)
                    'path'          => $rootPath,                  // path to files (REQUIRED)
                    'URL'           => $url,                        // URL to files (REQUIRED)
//                    'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => $this->options['uploadDeny'],
                    'uploadAllow'   => $this->options['uploadAllow'],
                    'uploadOrder'   => $this->options['uploadOrder'],
                    'accessControl' => 'access',                    // disable and hide dot starting files (OPTIONAL)
                ],
                // Trash volume
//                [
//                    'id'            => '1',
//                    'driver'        => 'Trash',
//                    'path'          => $rootPath . '/.trash/',
//                    'tmbURL'        => $url . '/.trash/.tmb/',
//                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
//                    'uploadDeny'    => ['all'],                     // Recommend the same settings as the original volume that uses the trash
//                    'uploadAllow'   => ['image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'], // Same as above
//                    'uploadOrder'   => ['deny', 'allow'],           // Same as above
//                    'accessControl' => 'access',                    // Same as above
//                ]
            ]
        ];

        parent::__construct($opts);

        // После создания, а то случается рекурсия
        if (!is_dir($rootPath) && $userId) {
            $this->storageService->createUserDirIfNotExists($userId, $this);
        }

        if (!is_dir($rootPath) || !is_writable($rootPath)) {
            exit(json_encode(['error' => 'Invalid backend configuration']));
        }

    }

    /************************************************************/
    /**                   elFinder commands                    **/
    /************************************************************/

    /**
     * Return current dir content to client or output file content to browser
     *
     * @param $args
     *
     * @return array
     * @throws elFinderAbortException
     */
    protected function open($args)
    {
        $this->result = parent::open($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;
        
        $this->readFile(); // came from old code, is it still need here?
        list(
            $this->result['cwd'  ],
            $this->result['files']
        ) = $this->linkWithStorageModel($this->result['cwd'], $this->result['files']);

        if (isset($this->result['changed'])) $this->result['changed'] = $this->updateChangedName($this->getTarget());
        return $this->result;
    }

    /**
     * Return subdirs for required directory
     *
     * @param  array  command arguments
     *
     * @return array
     **/
    protected function tree($args)
    {
        $this->result = parent::tree($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;
        
        if (($volume = $this->volume($args['target'])) == false
            || ($tree = $volume->tree($args['target'])) == false) {
            return array('error' => $this->error(self::ERROR_OPEN, '#' . $args['target']));
        }

        list(,$tree) = $this->linkWithStorageModel($tree[0], $tree);

        return array('tree' => $tree);
    }

    /**
     * Return parents dir for required directory
     *
     * @param  array  command arguments
     *
     * @return array
     * @throws elFinderAbortException
     */
    protected function parents($args)
    {
        $this->result = parent::parents($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;
        
        if (($volume = $this->volume($args['target'])) == false
            || ($tree = $volume->parents($args['target'], false, $args['until'])) == false) {
            return array('error' => $this->error(self::ERROR_OPEN, '#' . $args['target']));
        }

        list(,$tree) = $this->linkWithStorageModel($tree[0], $tree);

        return array('tree' => $tree);
    }

    /**
     * Rename file/folder
     *
     * @param $args
     * @return array
     *
     * @throws Zend_Exception
     * @throws elFinderAbortException
     */
    protected function rename($args)
    {
        $this->result = parent::rename($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;
        
        $newName = $this->result['added'][0]['name'];

        $item = $this->getTarget();

        if (!$item) {
            $this->result['error'] = 'Invalid parameters';
            return $this->result;
        }
        $name = iconv("UTF-8", Zend_Registry::get('config')->charset, trim($newName));

        if (!$res = $this->storageService->rename($name, $item, $this)) {
            $this->result['error'] = 'Unable to rename file';
            $this->content($res, false);
            return $this->result;
        }

        return $this->result;
    }

    /**
     * Create empty file
     *
     * @param array  command arguments
     *
     * @return array
     *
     * @throws Zend_Exception
     */
    protected function mkfile($args)
    {
        $this->result = parent::mkfile($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;

        $item = $parentDir = $this->storageService->getItem($args['target'], $this);
        if (!$item) {
            $this->result['error'] = 'Invalid parameters';
            return $this->result;
        }
        if (!$this->isAllowed($item->user_id, 'write', $item)) {
            $this->result['error'] = 'Access denied';
            return $this->result;
        }
        $name = iconv("UTF-8", Zend_Registry::get('config')->charset, trim($args['name']));
        if ($this->storageService->isExists($item, $name, false)) {
            $this->result['error'] = 'File or folder with the same name already exists';
            return $this->result;
        }
        $res = $this->storageService->saveFile($name, $item, $this);
        if ($res && $res->id) {
            $this->result['select']  = [$res->id];
            $this->content($res, true);
        } else {
            $this->result['error'] = 'Unable to create folder';
        }

        list(
            $this->result['cwd'  ],
            $this->result['files']
            ) = $this->linkWithStorageModel($this->result['cwd'], $this->result['files']);

        if (isset($this->result['changed'])) $this->result['changed'] = $this->updateChangedName($this->getTarget());

        $root = $this->storageService->getRoot($parentDir, $this);
        $volume = $this->getVolume($this->rootHash);
        $rootName = $volume->getRootName();
        $this->result['changed'][0] = [
            "hash"     => $parentDir->getValue('name') == $rootName ? $root->getValue('hash') : $parentDir->getValue('hash'),
            "isowner"  => false,
            "mime"     => "directory",
            "name"     => $parentDir->getValue('alias'),
            "phash"    => $parentDir->getValue('name') == $rootName ? $root->getValue('phash') : $parentDir->getValue('phash'),
            "read"     => 1,
            "size"     => 0,
            "ts"       => $this->utime(),
            "volumeid" => "l1_",
            "write"    => 1
        ];

        return $this->result;
    }

    /**
     * Create new folder
     *
     * @param $args
     *
     * @return array
     * @throws Zend_Exception
     */
    protected function mkdir($args)
    {
        $this->result = parent::mkdir($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;

        $item = $parentDir = $this->storageService->getItem($args['target'], $this);
        if (!$item) {
            $this->result['error'] = 'Invalid parameters';
            return $this->result;
        }
        if (!$this->isAllowed($item->user_id, 'write', $item)) {
            $this->result['error'] = 'Access denied';
            return $this->result;
        }
        $name = iconv("UTF-8", Zend_Registry::get('config')->charset, trim($args['name']));
        if ($this->storageService->isExists($item, $name, false)) {
            $this->result['error'] = 'File or folder with the same name already exists';
            return $this->result;
        }
        $res = $this->storageService->createDir($name, $item, $this);
        if ($res && $res->id) {
            $this->result['select']  = [$res->id];
            $this->content($res, true);
        } else {
            $this->result['error'] = 'Unable to create folder';
        }

        list(
            $this->result['cwd'  ],
            $this->result['files']
            ) = $this->linkWithStorageModel($this->result['cwd'], $this->result['files']);

        $root = $this->storageService->getRoot($parentDir, $this);
        $volume = $this->getVolume($this->rootHash);
        $rootName = $volume->getRootName();
        $this->result['changed'][0] = [
            "hash"     => $parentDir->getValue('name') == $rootName ? $root->getValue('hash') : $parentDir->getValue('hash'),
            "isowner"  => false,
            "mime"     => "directory",
            "name"     => $parentDir->getValue('alias'),
            "phash"    => $parentDir->getValue('name') == $rootName ? $root->getValue('phash') : $parentDir->getValue('phash'),
            "read"     => 1,
            "size"     => 0,
            "ts"       => $this->utime(),
            "volumeid" => "l1_",
            "write"    => 1
        ];

        return $this->result;
    }

    /**
     * Remove files/folders
     *
     * @param $args
     * @return array
     *
     * @throws elFinderAbortException
     */
    protected function rm($args)
    {
        if (self::UNPLUG_STORAGE_MODEL) {
            $this->result = parent::rm($args);
            return $this->result;
        }

        if (empty($args['targets']) || !is_array($args['targets'])) {
            $this->result['error'] = 'Invalid parameters';
            return $this->result;
        }

        $parentDir = null;
        foreach ($args['targets'] as $key => $hash) {
            $item = $this->storageService->getItem($hash, $this);

            if (!$item) {
                $this->result['error'] = 'Invalid parameters';
                return $this->result;
            }

            if (!$this->isAllowed($item->user_id, 'rm', $item)) {
                $this->result['error'] = 'Access denied';
                return $this->result;
            }
            if (!$parentDir) $parentDir = $this->storageService->getItem($item->getValue('phash'), $this);
            $this->result['removed'][] = ['hash' => $item->getValue('hash')];
            $res = $this->storageService->remove($item, $this);
            if ($res === false) {
                $this->result['error'] = 'Unable to remove file';
                return $this->result;
            }
        }
        $root = $this->storageService->getRoot($parentDir, $this);
        $volume = $this->getVolume($this->rootHash);
        $rootName = $volume->getRootName();
        $this->result['changed'][0] = [
            "hash"     => $parentDir->getValue('name') == $rootName ? $root->getValue('hash') : $parentDir->getValue('hash'),
            "isowner"  => false,
            "mime"     => "directory",
            "name"     => $parentDir->getValue('alias'),
            "phash"    => $parentDir->getValue('name') == $rootName ? $root->getValue('phash') : $parentDir->getValue('phash'),
            "read"     => 1,
            "size"     => 0,
            "ts"       => $this->utime(),
            "volumeid" => "l1_",
            "write"    => 1
        ];

        return $this->result;
    }

    /**
     * Upload files
     *
     * @param $args
     * @return array
     *
     * @throws elFinderAbortException
     */
    protected function upload($args)
    {
        $this->result = parent::upload($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;
        
        $dir = $this->storageService->getItem($args['target'], $this);

        if (!$this->isAllowed($dir->user_id, 'write', $dir)) {
            $this->result['error'] = 'Access denied';
            return $this->result;
        }
        if (empty($_FILES['upload'])) {
            $this->result['error'] = 'No file to upload';
            return $this->result;
        }
        $this->result['select'] = [];
        $this->result['rel'] = [];
        $total = 0;

        // remove all trash from prev unsuccessful upload
        if(in_array($this->subjectName, [
            HM_Storage_StorageModel::CONTEXT_SUBJECT_MATERIALS,
            HM_Storage_StorageModel::CONTEXT_SUBJECT_LESSONS,
            HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS,
        ])) {
            $storageItems = $this->storageService->fetchAll($this->storageService->quoteInto(
                [
                    '((subject_name = ? AND ',
                    'subject_id = ?) OR  ',
                    'parent_id = ?) AND ',
                    'is_file = ?',
                ],
                [
                    $this->subjectName,
                    $this->subjectId,
                    $dir->id,
                    HM_Storage_StorageService::ITS_FILE,
                ]
            ));

            foreach($storageItems as $storageItem) {
                $sourceFile = $this->storageService->decodeHash($storageItem->hash, $this);
                unlink($sourceFile);
                $this->storageService->delete($storageItem->id);
            }
        }

        for ($i=0, $s = count($_FILES['upload']['name']); $i < $s; $i++) {
            if (!empty($_FILES['upload']['name'][$i])) {
                $total++;
                $name = trim($_FILES['upload']['name'][$i]);
                if ($_FILES['upload']['error'][$i] > 0) {
                    $error = 'Unable to upload file';
                    switch ($_FILES['upload']['error'][$i]) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error = 'File exceeds the maximum allowed filesize';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $error = 'Not allowed file type';
                            break;
                    }
                    $this->errorData($_FILES['upload']['name'][$i], $error);
                } else {
                    if ($this->storageService->isExists($dir, $name, true)) {
                        $this->errorData($_FILES['upload']['name'][$i], 'File or folder with the same name already exists');
                    } elseif (!$this->isUploadAllow($_FILES['upload']['name'][$i], $_FILES['upload']['tmp_name'][$i])) {
                        $this->errorData($_FILES['upload']['name'][$i], 'Not allowed file type');
                    } else {
                        $res = $this->storageService->saveFile($name, $dir, $this);
                        if ($res === false) {
                            $this->errorData($_FILES['upload']['name'][$i], 'Unable to save uploaded file');
                        } else {
                            $path = $this->storageService->getUrl($dir, $this).'/'.$this->storageService->getAlias($name);
                            $this->result['rel'][] = $path;
                        }
                    }
                }

            }
        }

        $errCnt = !empty($this->result['errorData']) ? count($this->result['errorData']) : 0;

        if ($errCnt == $total) {
            $this->result['error'] = 'Unable to upload files';
        } else {
            if ($errCnt > 0) {
                $this->result['error'] = 'Some files was not uploaded';
            }

            if ($this->subjectName == HM_Storage_StorageModel::CONTEXT_SUBJECT_MATERIALS) {
                $this->storageService->syncMaterials($this->subjectId, $this);
            } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_LESSONS == $this->subjectName) {
                $this->result['lessons'] = $this->storageService->addLessons($this->subjectId, $this);
            } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS == $this->subjectName) {
                $this->result['extraMaterials'] = $this->storageService->addExtraMaterials($this->subjectId, $this);
            }
        }

        return $this->result;
    }

    /**
     * Duplicate files/folders
     *
     * @param $args
     * @return array
     *
     * @throws Zend_Exception
     * @throws elFinderAbortException
     */
    protected function duplicate($args)
    {
        $this->result = parent::duplicate($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;

        foreach ($args['targets'] as $target) {
            $file = $this->storageService->getItem($target, $this);

            if (!$this->isAllowed($file->user_id, 'write', $file)) {
                $this->result['error'] = 'Access denied';
                return $this->result;
            }
            $parent = $this->storageService->getParent($file);
            $this->storageService->duplicate($file, $parent, $this);
        }

        if ($this->subjectName == HM_Storage_StorageModel::CONTEXT_SUBJECT_MATERIALS) {
            $this->storageService->syncMaterials($this->subjectId, $this);
        } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_LESSONS == $this->subjectName) {
            $this->storageService->addLessons($this->subjectId, $this);
        } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS == $this->subjectName) {
            $this->storageService->addExtraMaterials($this->subjectId, $this);
        }

        return $this->result;
    }

    /**
     * Copy/move files/folders
     *
     * @param $args
     * @return array
     *
     * @throws Zend_Exception
     * @throws elFinderAbortException
     */
    protected function paste($args)
    {
        $this->result = parent::paste($args);
        if (self::UNPLUG_STORAGE_MODEL) return $this->result;

        $cut = !empty($args['cut']);
        $parentDir = $this->storageService->getItem($args['dst'], $this);
        if (!$parentDir) {
            $this->result['error'] = 'Invalid parameters';
            return $this->result;
        }

        if (!$this->isAllowed($parentDir->user_id, 'write', $parentDir)) {
            $this->result['error'] = 'Access denied';
            return $this->result;
        }

        foreach ($args['targets'] as $hash) {
            $item = $this->storageService->getItem($hash, $this);
            if (!$item || !$item->id) {
                $this->result['error'] = sprintf(_('Файл %s не найден'), $hash) && $this->content($parentDir, true);
                return $this->result;
            }
            if ($item->id == $parentDir->id) {
                $this->result['error'] = 'Unable to copy into itself' && $this->content($parentDir, true);
                return $this->result;
            } elseif ($this->storageService->isExists($parentDir, $item->name, $item->is_file)) {
                $this->result['error'] = 'File or folder with the same name already exists' && $this->content($parentDir, true);
                return $this->result;
            } elseif ($cut && !$this->isAllowed($item->user_id, 'rm', $item)) {
                $this->result['error'] = 'Access denied' && $this->content($parentDir, true);
                return $this->result;
            }

            if ($cut) {
                $this->result['removed'][] = ['hash' => $item->getValue('hash')];
                if (!($newItems = $this->storageService->move($item, $parentDir, $this))) {
                    $this->result['error'] = 'Unable to move files' && $this->content($parentDir, true);
                    return $this->result;
                }
            } elseif (!($newItems = $this->storageService->copy($item, $parentDir, $this))) {
                $this->result['error'] = 'Unable to copy files' && $this->content($parentDir, true);
                return $this->result;
            }
            $added = $this->getVolume($parentDir->getValue('hash'))->getAdded();
            foreach ($newItems as $newItem) {
                foreach ($added as &$addition) {
                    $newItemHash  = $this->storageService->decodeHash(is_array($newItem) ? $newItem['hash'] : $newItem->hash, $this);
                    $additionHash = $this->storageService->decodeHash($addition['hash'], $this);
                    if ($newItemHash == $additionHash) $addition['name'] = $newItem->getValue('alias');
                }
            }
            $this->getVolume($parentDir->getValue('hash'))->setAdded($added);
        }

        $root = $this->storageService->getRoot($parentDir, $this);
        $volume = $this->getVolume($this->rootHash);
        $rootName = $volume->getRootName();
        $hash  = $parentDir->getValue('name') == $rootName ? $root->getValue('hash') : $parentDir->getValue('hash');
        $phash = $parentDir->getValue('name') == $rootName ? $root->getValue('phash') : $parentDir->getValue('phash');
        $this->result['changed'][0] = [
            "hash"     => $hash,
            "isowner"  => false,
            "mime"     => "directory",
            "name"     => $parentDir->getValue('alias'),
            "phash"    => $phash,
            "read"     => 1,
            "size"     => 0,
            "ts"       => $this->utime(),
            "volumeid" => "l1_",
            "write"    => 1
        ];

        return $this->result;
    }

    /**
     * Return file/folder info
     *
     * @param object $item
     * @return mixed
     *
     * @throws Zend_Exception
     */
    protected function info($item = null)
    {
        $info = [];
        $subject = null;
        if ($item instanceof HM_Storage_StorageModel) {
            $path = $this->storageService->getPath($item, $this);
            $info['hash'] = $item->hash;
            if (is_null($item->phash)) {
                $cabinet = $this->storageService->getCabinet();
                $subjectService = null;
                try {
                    $subjectService = $this->getService(ucfirst($cabinet->subject_name));
                } catch (Exception $e) {}
                if ($cabinet->getActivitySubjectId() && $subjectService) {
                    if ($cabinet->subject_name) {
                        $subject = $subjectService->find($cabinet->subject_id)->current();
                        switch ($cabinet->subject_name) {
                            case 'subject' :
                            case 'subject-materials' :
                                $info['name'] = ($subject && $subject->name)? $subject->name : $this->rootTitle;
                                break;
                            case 'course' :
                                $info['name'] = ($subject && $subject->Title)? $subject->Title : $this->rootTitle;
                                break;
                            default :
                                $info['name'] = $this->rootTitle;
                        }
                    }
                } else {
                    $info['name'] = $this->rootTitle;
                }
            } else {
                $info['name'] = htmlspecialchars($item->getName() ?: '');
            }
            $info['mime'] = ($item->is_file) ? $this->mimetype($path) : 'directory';
            $info['date'] = date($this->options['dateFormat'], strtotime($item->changed));
            $info['size'] = 1;//($item->is_file) ? $this->fileSize($path) : 0;//$this->dirSize($path)
            $info['comment'] = iconv(Zend_Registry::get('config')->charset, "UTF-8", $item->description.'');
            $info['read'] = true;
            $info['write'] = $this->isAllowed($item->user_id, 'write', $item);
            $info['rm'] = $this->isAllowed($item->user_id, 'rm', $item);
            if ($item->is_file) {
                $info['url'] = $this->storageService->getUrl($item, $this);
                $pathInfo = pathinfo($info['url']);
                if (in_array($pathInfo['extension'], ['jpg', 'jpeg', 'gif', 'bmp', 'png'])) {
                    $info['tmb'] = $info['url'];
                }
            }
        }

        return $info;
    }

    /************************************************************/
    /**                    "content" methods                   **/
    /************************************************************/
    /**
     * Set current dir info, content and [dirs tree]
     *
     * @param HM_Storage_StorageModel $dir current dir
     * @param bool $tree set dirs tree?
     * @return array
     *
     * @throws Zend_Exception
     */
    private function content($dir, $tree = false)
    {
        $this->result['cwd'] = $this->info($dir);
//        $this->result['cwd']['rel'] = $this->storageService->getUrl($dir, $this);

//        $this->cdc($dir);
        if ($tree) {
            //select root folders tree
            $root = $this->storageService->getRoot($dir, $this);
            $treeInfo = $this->info($root);
            $treeInfo['dirs'] = [];
            $dirs = $this->storageService->getChilds($root, true);

            foreach ($dirs as $dir) {
                if ($dir->alias != $this->usersFolders) {
                    $treeInfo['dirs'][] = $this->storageService->getChildsTree($dir, true);
                }
            }

            //select root folders
            $uRoot = $this->storageService->getUsersRoot($this);
            $treeRootInfo = $this->info($uRoot);
            $treeRootInfo['dirs'] = [];
            $dirs = $this->storageService->getUsersRootContent($this, true);

            foreach ($dirs as $dir) {
                $treeRootInfo['dirs'][] = $this->storageService->getChildsTree($dir);
            }
            $users = $this->getActivityUsers();

            foreach ($users as $user) {
                $itemInfo = $this->info($user);
                $itemInfo['dirs'] = [];
                $root = $this->storageService->getRoot($user, $this);
                if (!$root) {
                    $this->result['error'] = "Can't create root folder for user ".$user->getName()." (".$user->MID.")";
                    return $this->result;
                }
                $dirs = $this->storageService->getChilds($root);
                foreach ($dirs as $dir) {
                    $itemInfo['dirs'][] = $this->storageService->getChildsTree($dir);
                }
                $treeRootInfo['dirs'][] = $itemInfo;
            }

            $treeInfo['dirs'][] = $treeRootInfo;
            $this->result['tree'] = $treeInfo;
        }

        return $this->result;
    }

    /**
     * @param $cwd
     * @param $files
     *
     * @return array
     */
    private function linkWithStorageModel($cwd, $files)
    {
        if ($this->subjectName == HM_Storage_StorageModel::CONTEXT_SUBJECT_MATERIALS) {
            $this->storageService->syncMaterials($this->subjectId, $this);
        } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_LESSONS == $this->subjectName) {
            $this->storageService->addLessons($this->subjectId, $this);
        } elseif(HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS == $this->subjectName) {
            $this->storageService->addExtraMaterials($this->subjectId, $this);
        }

        foreach ($files as $key => &$file) {
            $item = $this->storageService->getItem($file['hash'], $this);
            if (!$item) {
                if ($file['name'] == $this->usersFolders) {
                    $item = $this->storageService->getUsersRoot($this);
                } else {
                    $user = $this->getService('User')->getCurrentUser() ?: $file;
                    $item = $this->storageService->getRoot(
                        (!$this->subjectId && !$this->isModerator) ? $user : $file, $this
                    );
                }
            }
            try {
                $this->content($item, false);
            } catch (Exception $e) {}

            if ($cwd['name'] == $file['name'])
                $cwd['name']  = $this->result['cwd']['name'];

            $file['name'] = $this->result['cwd']['name'];
        }

        return [$cwd, $files];
    }

    private function updateChangedName($dir)
    {
        foreach ($this->result['changed'] as &$changed) {
            $item = $this->storageService->getItem($changed['hash'], $this);
            if ($item->getValue('hash') === $dir->getValue('hash')) $changed['name'] = $item->alias;
        }
    }

    private function getActivityUsers()
    {
        $cabinet = $this->storageService->getCabinet();
        /**
         * должнен быть фильтр всегда для модераторов, независимо от включения сервиса
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 24.01.2013
         */
        $onlyModer = true;
        if ($cabinet  && in_array(ucfirst($cabinet->subject_name), ['Subject', 'Resource', 'Course'])) {
            if ($subject = $this->getService(ucfirst($cabinet->subject_name))->find($cabinet->subject_id)->current()) {
                if (!($subject->services & HM_Activity_ActivityModel::ACTIVITY_LIBRARY)) {
                    $onlyModer = true;
                }

                //#8051 для модулей в разработке отображать только личные папки Модераторов
                if ($cabinet->subject_name == 'course' && $subject->Status == HM_Course_CourseModel::STATUS_DEVELOPED) {
                    $onlyModer = true;
                }
            }
        } else {
            $activity = unserialize($this->getService('Option')->getOption('activity'));
            if (!isset($activity[HM_Activity_ActivityModel::ACTIVITY_LIBRARY])) {
                $onlyModer = true;
            }
        }

        if ($this->activityUsersCache) {
            $users = $this->activityUsersCache;
        } else {
            $users =
            $this->activityUsersCache = $this->storageService->getActivityUsers(
                $onlyModer,
                (!$this->isModerator && !$this->subjectId) ? true : false
            );
        }

        return $users;
    }

    /**
     * Set current dir content
     *
     * @param object $item
     * @return array
     *
     * @throws Exception
     */
    private function cdc($item)
    {
        if ($item->name == $this->usersFolders) {
            $items = $this->getActivityUsers();
            $content = $this->toArrays($items);
            $tmp = $this->toArrays($this->storageService->getUsersRootContent($this));
            $content[0] = array_merge($content[0], $tmp[0]);
            $content[1] = array_merge($content[1], $tmp[1]);
        } else {
            $items = $this->storageService->getContent($item->id);
            $content = $this->toArrays($items, $item);
        }

        $this->result['cdc'] = array_merge($content[0], $content[1]);
        return $this->result;
    }


    /************************************************************/
    /**                          Utilities                     **/
    /************************************************************/

    /**
     * Return file mimetype
     *
     * @param  string  $path  file path
     * @return string
     **/
    private function mimetype($path)
    {
        if (empty($this->options['mimeDetect']) || $this->options['mimeDetect'] == 'auto') {
            $this->options['mimeDetect'] = $this->getMimeDetect();
        }

        switch ($this->options['mimeDetect']) {
            case 'finfo':
                if (empty($this->fileInfo)) {
                    $this->fileInfo = finfo_open(FILEINFO_MIME);
                }
                $type = @finfo_file($this->fileInfo, $path);
                break;
            case 'php':
                $type = mime_content_type($path);
                break;
            case 'linux':
                $type = exec('file -ib '.escapeshellarg($path));
                break;
            case 'bsd':
                $type = exec('file -Ib '.escapeshellarg($path));
                break;
            default:
                $pinfo = pathinfo($path);
                $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
                $type = isset($this->mimetypes[$ext]) ? $this->mimetypes[$ext] : 'unknown;';
        }
        $type = $type ? explode(';', $type) : ['text/plain'];

        if ($this->options['mimeDetect'] != 'internal' && $type[0] == 'application/octet-stream') {
            $pinfo = pathinfo($path);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            if (!empty($ext) && !empty($this->mimetypes[$ext])) {
                $type[0] = $this->mimetypes[$ext];
            }
        }

        return $type[0];
    }

    /**
     * Return mimetype detect method name
     *
     * @return string
     **/
    private function getMimeDetect()
    {
        if (class_exists('finfo')) {
            return 'finfo';
        } elseif (function_exists('mime_content_type') && (mime_content_type(__FILE__) == 'text/x-php' || mime_content_type(__FILE__) == 'text/x-c++')) {
            return 'mime_content_type';
        } elseif (function_exists('exec')) {
            $type = exec('file -ib '.escapeshellarg(__FILE__));
            if (0 === strpos($type, 'text/x-php') || 0 === strpos($type, 'text/x-c++'))
            {
                return 'linux';
            }
            $type = exec('file -Ib '.escapeshellarg(__FILE__));
            if (0 === strpos($type, 'text/x-php') || 0 === strpos($type, 'text/x-c++'))
            {
                return 'bsd';
            }
        }
        return 'internal';
    }

    /**
     * Path error message in $this->result['errorData']
     *
     * @param string  $path  path to file
     * @param string  $msg   error message
     * @return bool always false
     **/
    private function errorData($path, $msg)
    {
        $path = preg_replace('|^'.preg_quote($this->options['root']).'|', $this->_fakeRoot, $path);
        if (!isset($this->result['errorData'])) {
            $this->result['errorData'] = [];
        }
        $this->result['errorData'][$path] = $msg;
        return false;
    }

    protected function utime()
    {
        $time = explode(" ", microtime());
        return (double)$time[1] + (double)$time[0];
    }

    /**
     * Return true if file's mimetype is allowed for upload
     *
     * @param  string  $name    file name
     * @param  string  $tmpName uploaded file tmp name
     * @return bool
     **/
    private function isUploadAllow($name, $tmpName)
    {
        $mime  = $this->mimetype($this->options['mimeDetect'] != 'internal' ? $tmpName : $name);
        $allow = false;
        $deny  = false;

        if (in_array('all', $this->options['uploadAllow'])) {
            $allow = true;
        } else {
            foreach ($this->options['uploadAllow'] as $type) {
                if (0 === strpos($mime, $type)) {
                    $allow = true;
                    break;
                }
            }
        }

        if (in_array('all', $this->options['uploadDeny'])) {
            $deny = true;
        } else {
            foreach ($this->options['uploadDeny'] as $type) {
                if (0 === strpos($mime, $type)) {
                    $deny = true;
                    break;
                }
            }
        }
        return 0 === strpos($this->options['uploadOrder'], 'allow') ? $allow && !$deny : $allow || !$deny;
    }

    /**
     * Return true if required action allowed to file/folder on file system
     *
     * @param  string  $path    file/folder path
     * @param  string  $action  action name (read/write/rm)
     * @return mixed
     **/
    private function isAllowedByFileSystem($path, $action) {
        switch ($action) {
            case 'read':
                if (!is_readable($path)) {
                    return false;
                }
                break;
            case 'write':
                if (!is_writable($path)) {
                    return false;
                }
                break;
            case 'rm':
                if (!is_writable(dirname($path))) {
                    return false;
                }
                break;
            default:
                return true;
        }

        $path = substr($path, strlen($this->options['root'])+1);
        foreach ($this->options['perms'] as $regex => $rules) {
            if (preg_match($regex, $path)) {
                if (isset($rules[$action])) {
                    return $rules[$action];
                }
            }
        }
        return isset($this->options['defaults'][$action]) ? $this->options['defaults'][$action] : false;
    }

    /**
     * Return true if required action allowed to file/folder
     *
     * @param string $userId
     * @param string $action action name (read/write/rm)
     * @param null   $item
     * @return mixed
     */
    private function isAllowed($userId, $action, $item = null)
    {
        switch ($action) {
            case 'read':
                return true;
            case 'write':
                if (null !== $item) {
                    return ((!$item->user_id && $this->storageService->isCurrentUserActivityModerator()) ||
                        ($item->user_id && $userId == $this->getService('User')->getCurrentUserId()));

                } else {
                    return ($this->storageService->isCurrentUserActivityModerator() ||
                        $userId == $this->getService('User')->getCurrentUserId());
                }
            case 'rm':
                if (null !== $item) {
                    return ((!$item->user_id && $this->storageService->isCurrentUserActivityModerator()) ||
                        ($item->user_id && $userId == $this->getService('User')->getCurrentUserId()));
                } else {
                    return ($this->storageService->isCurrentUserActivityModerator() ||
                        $userId == $this->getService('User')->getCurrentUserId());
                }
            default:
                return false;
        }
    }

    /**
     * Count total directory size if this allowed in options
     *
     * @param string $path directory path
     * @return int
     *
     * @throws Exception
     */
    // private function dirSize($path)
    // {
    // $size = 0;
    // if (!$this->options['dirSize'] || !$this->isAllowedByFileSystem($path, 'read')) {
    // return filesize($path);
    // }
    // if (!isset($this->options['du'])) {
    // $this->options['du'] = function_exists('exec')
    // ? exec('du -h '.escapeshellarg(__FILE__), $o, $s) > 0 && $s == 0
    // : false;
    // }
    // if ($this->options['du']) {
    // $size = intval(exec('du -k '.escapeshellarg($path)))*1024;
    // } else {
    // $ls = scandir($path);
    // for ($i=0; $i < count($ls); $i++) {
    // if ($this->_isAccepted($ls[$i])) {
    // $p = $path.DIRECTORY_SEPARATOR.$ls[$i];
    // $size += filetype($p) == 'dir' && $this->_   isAllowedByFs($p, 'read') ? $this->dirSize($p) : filesize($p);
    // }
    // }
    // }
    // return $size;
    // }

    private function fileSize($path)
    {
        if (is_dir($path)) {
            throw new Exception('It is not file');
        }
        @$stat = stat($path);
        return $stat['size'];
    }

    private function getTarget()
    {
        $target = Zend_Controller_Front::getInstance()->getRequest()->getParam('target', 0);
        $item = $this->storageService->getItem($target, $this);
        if (!$item) {
            header('HTTP/1.x 404 Not Found');
            exit('Target '.$target.' not found');
        }
        return $item;
    }

    /**
     * Send header Connection: close. Required by safari to fix bug http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
     *
     * @return void
     **/
    private function ping()
    {
        exit(header("Connection: close"));
    }

    private function comment()
    {
        $item = $this->getTarget();
        if (!$item || !isset($_POST['content'])) {
            return $this->result['error'] = 'Invalid parameters';
        }
        $comment = strip_tags(trim($_POST['content']));
        $comment = iconv("UTF-8", Zend_Registry::get('config')->charset, $comment);
        $comment = substr($comment, 0, 255);
        $this->storageService->update([
            'id' => $item->id,
            'description' => $comment
        ]);
    }

    private function toArrays($items, $parent = false)
    {
        $dirs = $files = [];
        foreach ($items as $item) {
            if ($item->alias != $this->usersFolders && $parent && !$parent->parent_id && (($item->subject_id != $this->subjectId) || ($item->subject_name != $this->subjectName))) {
                continue;
            }
            try {
                $info = $this->info($item);
            } catch (Zend_Exception $e) {
            }
            if ($info['mime'] == 'directory') {
                $dirs[] = $info;
            } else {
                $files[] = $info;
            }
        }
        return [$dirs, $files];
    }

    private function getService($serviceName)
    {
        return Zend_Registry::get('serviceContainer')->getService($serviceName);
    }

    private function readFile()
    {
        if (isset($args['target'])) { // read file
            //TODO add ACL checking
            $item = $this->getTarget();
            $file = $this->storageService->getPath($item, $this);
            if (!file_exists($file)) {
                header('HTTP/1.x 404 Not Found');
                exit(sprintf(_('Файл %s не найден'), $file));
            }
//            $mime  = $this->mimetype($file);
//            $parts = explode('/', $mime);
//            $disp  = $parts[0] == 'image' || $parts[0] == 'text' ? 'inline' : 'attachments';
            $disp  = 'attachments';

            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private',false);

            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');

            header("Content-Disposition: ".$disp."; filename=".basename($file));
            header("Content-Location: ".$this->storageService->getUrl($item, $this));
            header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , strtotime($item->changed)));
            header('Content-Transfer-Encoding: binary');
            header("Content-Length: ".filesize($file));
            header("Connection: close");
            readfile($file);
            exit();
        }
    }

    public function exec($cmd, $args)
    {
        $result = parent::exec($cmd, $args);
        if (count($result['changed'])) {
            foreach ($result['changed'] as &$changed) {
                foreach ($this->result['changed'] as $res) {
                    $r = $this->storageService->decodeHash($res['hash'], $this);
                    $c = $this->storageService->decodeHash($changed['hash'], $this);
                    if ($r == $c) $changed['name'] = $res['name'];
                }
            }
        }
        if (count($result['removed'])) {
            foreach ($result['removed'] as &$removed) {
                foreach ($this->result['removed'] as $res) {
                    $r1 = $this->storageService->decodeHash($res['hash'], $this);
                    $c1 = $this->storageService->decodeHash($removed, $this);
                    if ($r1 == $c1) $removed = $this->storageService->encodeHash($c1, $this);
                }
            }
        }
        return $result;
    }
}
