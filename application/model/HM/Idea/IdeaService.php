<?php
class HM_Idea_IdeaService extends HM_Service_Abstract
{
    public function getPopulatedFiles($ideaId)
    {
        $populatedFiles = array();
        $files = $this->getService('Files')->fetchAll(array('item_id = ?' => $ideaId, 'item_type = ?' => HM_Files_FilesModel::ITEM_TYPE_IDEA));
        foreach($files as $file)
        {
            $files_service = $this->getService('Files');
            $file_s = $files_service->fetchAll("file_id = '{$file->file_id}'");

            $populatedFiles[] = new HM_File_FileModel(array(
                'id' => $file->file_id,
                'displayName' => $file_s[0]->name,
                'path' => $files_service->getPath($file->file_id),
                'url' => Zend_Registry::get('view')->url(array('module' => 'file', 'controller' => 'get', 'action' => 'file', 'file_id' => $file->file_id))));
        }                                  
        return $populatedFiles;
    }

}