<?php

class State_EditController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_state = '';
    protected $_stateId = 0;
    protected $_field = '';

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Тьютор успешно создан'),
            self::ACTION_UPDATE    => _('Тьютор успешно обновлён'),
            self::ACTION_DELETE    => _('Тьютор успешно удалён'),
            self::ACTION_DELETE_BY => _('Тьюторы успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE => _('Тьютор не был создан'),
            self::ERROR_NOT_FOUND        => _('Тьютор не найден')
        );
    }

    public function init()
    {
        parent::init();

        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('edit', 'json')
            ->initContext('json');

        $this->_state   = $this->_getParam('state' , '');
        $this->_stateId = $this->_getParam('stateId', 0);
        $this->_field   = $this->_getParam('field', '');

        $this->_initForm();

    }

    protected function _redirectToIndex()
    {

    }

    protected function _initForm()
    {
            $form = null;

        switch ($this->_field) {
            case 'comment':
                $form = new HM_State_Form_CommentForm();
                break;

            case 'file':
                $form = new HM_State_Form_FilesForm();
                break;
        }

        if ($form) {
            $this->_setForm($form);
        }
    }

    public function setDefaults(HM_Form $form)
    {
        /** @var HM_State_Data_StateDataService $stateDataService */
        $stateDataService = $this->getService('StateData');
        /** @var HM_Files_FilesService $fileService */
        $fileService = $this->getService('Files');

        $stateData = $stateDataService->fetchAll(array(
            'state = ?' => $this->_state,
            'state_of_process_id = ?' => $this->_stateId
        ));

        $values = array(
            'comment' => $stateData->comment
        );

        $values['files'] = $fileService->getItemFiles(
            HM_Files_FilesModel::ITEM_TYPE_PROCESS_STATE_DATA,
            $stateData->state_of_process_data_id
        );

        $form->populate($values);

    }

    public function update($form=false, $updateLastPassedState = true)
    {
        /** @var HM_State_StateService $stateService */
        $stateService = $this->getService('State');
        $state = $stateService->fetchAll(array(
            'state_of_process_id = ?' => $this->_stateId
        ));

        $state = $this->getOne($state);
        if ($updateLastPassedState) $state->last_passed_state = $this->_state;
        $stateService->update($state->getData());

        /** @var HM_State_Data_StateDataService $stateDataService */
        $stateDataService = $this->getService('StateData');
        $stateData = $stateDataService->fetchAll(array(
            'state = ?' => $this->_state,
            'state_of_process_id = ?' => $this->_stateId
        ));

        $stateData = $this->getOne($stateData);

        if (!$stateData) {
            // для обратной совместимости со старыми процессами вставляем ненайденную запись
            // ВНИМАНИЕ! теперь этот случай невозможен, все $stateData создаются при старте процесса
            $stateData = $stateDataService->insert(array(
                'state'               => $this->_state,
                'state_of_process_id' => $this->_stateId,
                'begin_date'          => HM_Date::now()->toString(HM_Date::SQL),
                'begin_by_user_id'    => 0,
                'begin_auto'          => 0,
                'status'              => HM_Process_Abstract::PROCESS_STATUS_CONTINUING
            ));
        }

        if($form) {
            $values = $form->getValues();
        }

        switch ($this->_field) {
            case 'comment':
                $now = new HM_Date();
                $now = $now->toString(HM_Date::SQL);

                $stateDataService->update(array(
                    'state_of_process_data_id' => $stateData->state_of_process_data_id,
                    'comment_user_id' => $this->getService('User')->getCurrentUserId(),
                    'comment_date' => $now,
                    'comment' => $values['comment']
                ));
                break;
            case 'date':

                // позволяем менять даты задним числом (?)

                $begin_date = $this->_getParam('startDate', '') ;   
                $end_date = $this->_getParam('endDate', '');    
                $data = array('state_of_process_data_id' => $stateData->state_of_process_data_id);
                if($begin_date) {
                    $data['begin_date_planned'] = $data['begin_date'] = $begin_date;
                }
                if($end_date) {
                    $data['end_date_planned'] = $data['end_date'] = $end_date;
                }
                if(count($data)>1) {
                    $stateDataService->update($data);
                }
// нет нужды менять даты event'ов
//                $state = $this->getService('State')->find($this->_stateId)->current();
//                $stateParams = unserialize($state->params);
//                $sessionEventArray = array_keys($stateParams[$this->_state]['session_events']);
//                $sessionEvent = $sessionEventArray[0] ? $sessionEventArray[0] : null;
//                try {
//                    $eventModel = $this->getService('AtSessionEvent')->find($sessionEvent)->current();
//                    $this->getService('AtSessionEvent')->update(array(
//                        'session_event_id' => $eventModel->session_event_id,
//                        'date_begin' => date('Y-m-d', strtotime($begin_date)),
//                        'date_end' => date('Y-m-d', strtotime($end_date))
//                    ));
//                } catch (Exception $e) {}
                break;

            case 'file':
                $this->_addFiles($form, $stateData->state_of_process_data_id);
                break;
        }

    }

    protected function _addFiles(HM_Form $form, $stateOfProcessDataId)
    {
        /** @var HM_Files_FilesService $fileService */
        $fileService = $this->getService('Files');

        /** @var Zend_File_Transfer_Adapter_Http $files */
        $files = $form->files;

        // нужно физически удалить файлы, которые удалили из формы нажатием на "х"
        $populatedFiles = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_PROCESS_STATE_DATA, $stateOfProcessDataId);
        $deletedFiles   = $form->files->updatePopulated($populatedFiles);
        if(count($deletedFiles)) {
            $this->getService('Files')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
            switch ($this->_state) {
                case 'HM_Recruit_Newcomer_State_Plan':
                    $this->getService('RecruitNewcomerFile')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
                    break;
                case 'HM_Hr_Rotation_State_Plan':
                    $this->getService('HrRotationFile')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
                    break;
                case 'HM_Hr_Reserve_State_Plan':
                    $this->getService('HrReserveFile')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
                    break;
            }
        }

        if ($files->isUploaded() && $files->receive() && $files->isReceived()) {

            $files = $files->getFileName();

            if (!is_array($files)) {
                $files = array($files);
            }

            $model = null;
            $modelData = null;

            $state = null;
            foreach ($files as $file) {
                $fileInfo = pathinfo($file);
                $fileData = $fileService->addFile(
                    $file,
                    $fileInfo['basename'],
                    HM_Files_FilesModel::ITEM_TYPE_PROCESS_STATE_DATA,
                    $stateOfProcessDataId
                );

                switch ($this->_state) {
                    case 'HM_Recruit_Newcomer_State_Open':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('RecruitNewcomerFile')->insert(array(
                            'state_type' => HM_Recruit_Newcomer_File_FileModel::STATE_TYPE_OPEN,
                            'newcomer_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;
                    case 'HM_Hr_Rotation_State_Open':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('HrRotationFile')->insert(array(
                            'state_type' => HM_Hr_Rotation_File_FileModel::STATE_TYPE_OPEN,
                            'rotation_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;
                    case 'HM_Hr_Reserve_State_Open':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('HrReserveFile')->insert(array(
                            'state_type' => HM_Hr_Reserve_File_FileModel::STATE_TYPE_OPEN,
                            'reserve_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;
                    case 'HM_Recruit_Newcomer_State_Plan':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('RecruitNewcomerFile')->insert(array(
                            'state_type' => HM_Recruit_Newcomer_File_FileModel::STATE_TYPE_PLAN,
                            'newcomer_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;

                    case 'HM_Recruit_Newcomer_State_Publish':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('RecruitNewcomerFile')->insert(array(
                            'state_type' => HM_Recruit_Newcomer_File_FileModel::STATE_TYPE_PUBLISH,
                            'newcomer_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;
                    case 'HM_Hr_Rotation_State_Publish':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('HrRotationFile')->insert(array(
                            'state_type' => HM_Hr_Rotation_File_FileModel::STATE_TYPE_PUBLISH,
                            'rotation_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;
                    case 'HM_Hr_Reserve_State_Publish':
                        if (!$state) {
                            $state = $this->getOne($this->getService('State')->find($this->_stateId));
                        }
                        $this->getService('HrReserveFile')->insert(array(
                            'state_type' => HM_Hr_Reserve_File_FileModel::STATE_TYPE_PUBLISH,
                            'reserve_id' =>  $state->item_id,
                            'file_id' => $fileData->file_id
                        ));
                        break;
                }
            }
        }
    }

    public function editAction()
    {
        if($this->_getParam('field', '')=='date') { //Особый путь сохранения
            $this->update();
            die();
        }


        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                if($this->_getParam('field', '')=='comment') {
                    $this->update($form, false);
                } else {
                    $this->update($form);
                }

                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

}