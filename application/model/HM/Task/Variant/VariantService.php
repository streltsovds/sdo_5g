<?php 
class HM_Task_Variant_VariantService extends HM_Service_Abstract
{

    public function delete($id)
    {
        $this->getService('Files')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($id, HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT)));
        return parent::delete($id);
    }
/*

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => 0, //public
        );
    }
*/

    public function getPopulatedFiles($variantId)
    {
        $populatedFiles = array();
        $files = $this->getService('Files')->fetchAll(array('item_id = ?' => $variantId, 'item_type = ?' => HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT));
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

    public function copy($variant, $newTaskId)
    {
        $variant->task_id = $newTaskId;
        $newVariant = $this->insert($variant->getValues(null, ['variant_id']));

        /** @var HM_Files_FilesService $filesService */
        $filesService = $this->getService('Files');

        $files = $filesService->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_TASK_VARIANT, $variant->variant_id, false);
        if($files) {
            foreach ($files as $file) {
                $filesService->copy($file, ['item_id' => $newVariant->variant_id]);
            }
        }
    }

}