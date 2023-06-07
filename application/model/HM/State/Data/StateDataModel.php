<?php

class HM_State_Data_StateDataModel extends HM_Model_Abstract
{
    public function getFiles()
    {
        /** @var HM_Files_FilesService $fileService */
        $fileService = Zend_Registry::get('serviceContainer')->getService('Files');

        return $fileService->getItemFiles(
            HM_Files_FilesModel::ITEM_TYPE_PROCESS_STATE_DATA,
            $this->state_of_process_data_id,
            true,
            true
        );
    }

}