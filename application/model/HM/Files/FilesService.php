<?php
class HM_Files_FilesService extends HM_Service_Abstract
{
    public function addFile($filePath, $fileNameString, $itemType = false, $itemId = false, $dest = '')
    {
        if($dest == ''){
            $dest = realpath(Zend_Registry::get('config')->path->upload->files);
        }
        $fileName = basename($filePath);
        $temp = explode('.', $fileName);
        $ext = $temp[count($temp) - 1];
        $data = [
            'name'      => $fileNameString,
            'path'      => 'none',
            'file_size' => filesize($filePath)
        ];

        if (!empty($itemId)) {
            $data['item_id'] = $itemId;
            $data['item_type']    = $itemType;
        }

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $data['created']    = HM_Date::now()->toString(HM_Date::SQL);
        $data['created_by'] = $userService->getCurrentUserId();

        $fileData = $this->insert($data);

        if(!$fileData)
            return false;

        $destFile = ( count ($temp) > 1 )? $dest . '/' . $fileData->file_id . '.' . $ext : $dest . '/' . $fileData->file_id . '.tmp';
        copy($filePath, $destFile);

        return $fileData;
    }

    public function addClip($filePath, $fileNameString)
    {
        $fileData = $this->insert(
            array(
                'name'      => $fileNameString,
                'path'      => $filePath,
                'file_size' => 0
            )
        );
        if(!$fileData)
            return false;
        return $fileData;
    }

    public function addFileFromBinary($binaryData, $fileNameString, $itemType = false, $itemId = false)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "Binary_");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $binaryData);
        fclose($handle);
        $file = $this->addFile($tmpfname, $fileNameString, $itemType, $itemId);
        unlink($tmpfname);
        return $file;
    }

    public static function getPath($fileId)
    {
        $dest = realpath(Zend_Registry::get('config')->path->upload->files);
        $glob = glob($dest . '/' . $fileId . '.*');

        return realpath($glob[0]);
    }

    public function delete($id)
    {
        $path = self::getPath($id);
        if (file_exists($path)) {
            @unlink($path);
        }

        return parent::delete($id);
    }

    public function deleteBy($where)
    {
        $files = $this->fetchAll($where);

        foreach ($files as $file) {
            $this->delete($file->file_id);
        }
    }


    // $file->getMimeType() возвращает application/octet-stream
    // поэтому приходится вычислять вручную


    //получаем файлы по привязке к объекту
    public function getItemFiles($itemType, $itemId, $populated = true, $createdBy = false)
    {
        if (!$itemId) return [];
        $files = $this->fetchAll(
            array(
                'item_type = ?'    => $itemType,
                'item_id = ?' => $itemId
            ));
        if (!$populated) {
            return $files;
        }

        $populatedFiles = array();
        foreach($files as $file)
        {
            $data = array(
                'id'          => $file->file_id,
                'displayName' => $file->name,
                'size'        => $file->file_size,
                'path'        => $this->getPath($file->file_id),
                'url'         => Zend_Registry::get('view')->url(array('module' => 'file', 'controller' => 'get', 'action' => 'file', 'file_id' => $file->file_id, 'baseUrl' => '')),
            );

            if ($createdBy) {
                $data['created_by'] = $file->created_by;
            }

            $populatedFiles[] = new HM_File_FileModel($data);

        }

        return $populatedFiles;
    }

    static public function detectEncoding($str)
    {
        $encodings = array('UTF-8', 'Windows-1251');
        foreach($encodings as $encoding) {
            if ($str == iconv($encoding, $encoding, $str)) {
                return $encoding;
            }
        }
        return 'UTF-8';
    }

    static public function detectFileEncoding($filename)
    {
        if ($content = file_get_contents($filename)) {
            $content = iconv(self::detectEncoding($content), Zend_Registry::get('config')->charset, $content);
            if (strlen($content)) {
                if (strtolower(Zend_Registry::get('config')->charset) == 'utf-8') {
                    $content = preg_replace('/\xEF\xBB\xBF/', '', $content);
                }
                if (is_writeable($filename)) {
                    file_put_contents($filename, $content);
                }
            }
        }
    }

    /*
     * 5G
     * Универсальный метод для загрузки и распаковки файла
     * SCORM или HTML-сайт или что угодно
     *
     */
    public function unzip($filename, $delete = true)
    {
        $fileType = HM_Files_FilesModel::getFileType($filename);

        if ($fileType == HM_Files_FilesModel::FILETYPE_ZIP) {
            // Чтобы параллельные импорты и неудалённые файлы друг другу не мешались
            $unzipPath = realpath(Zend_Registry::get('config')->path->upload->tmp) . DS . 'unzip_' . time();
            if (!is_dir($unzipPath)) {
                mkdir($unzipPath, 0755);
            } else {
                $this->getService('Course')->emptyDir($unzipPath);
            }

            $filter = new Zend_Filter_Decompress([
                'adapter' => 'Zip',
                'options' => ['target' => $unzipPath]
            ]);

            $filter->filter($filename);

            if($delete) {
                unlink($filename);
            }

            return $unzipPath;
        }

        return false;
    }

    /**
     * @param $file
     * @param array $newData
     */
    public function copy($file, $newData)
    {
        $oldPath = $this->getPath($file->file_id);

        foreach ($newData as $key => $value) {
            $file->{$key} = $value;
        }

        $newFile = $this->insert($file->getValues(null, ['file_id']));
        $newPath = str_replace($file->file_id, $newFile->file_id, $oldPath);

        copy($oldPath, $newPath);
    }
}