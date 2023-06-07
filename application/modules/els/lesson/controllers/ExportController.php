<?php
class Lesson_ExportController extends HM_Controller_Action
{
    protected $_module = 'lesson';
    protected $_controller = 'export';
    
    
    protected function _initExportCsv($title)
    {
        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-Type', 'text/csv', true);
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$title.'.csv"', true);   
    }
    
    
    public function csvAction()
    {
        
        $subjectId = (int) $this->_getParam('subject_id', 0);
        
        $student = false;

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $student = $this->getService('User')->getCurrentUserId();
        }
        
        if($student){
            $this->_initExportCsv($student.'_'.$subjectId.'_result');
            
            $result = $this->getService('LessonCsv')->getStudentResults($subjectId, $student);
            
            $collection = $this->getService('SubjectMark')->fetchAll(
                $this->getService('SubjectMark')->quoteInto(
                    array('CID = ?', ' AND MID = ?'),
                    array($subjectId, ($student)? $student : $this->getService('User')->getCurrentUserId())
                )
            );

            $mark = count($collection) ? $this->getOne($collection)->mark : HM_Scale_Value_ValueModel::VALUE_NA;
            
            $csv = "";
            foreach ($result as $value){
                $csv .= implode(';', array(
                    $value['MID'],
                    $value['CID'],
                    $value['SHEID'],
                    $value['V_STATUS'],
                    $mark,
                    $value['SSID']
                ));
                $csv .= ";\r\n";
            }
            $this->view->csv = $csv;
        }
    }
    
}