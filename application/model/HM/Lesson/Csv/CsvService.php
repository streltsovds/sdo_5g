<?php
class HM_Lesson_Csv_CsvService extends HM_Service_Abstract
{
    private function mapData($data){
        
        if(!empty($data)){
            $result = array('LessonAssign' => array());
            $result['LessonAssign']['MID']          = $data['0'];
            $result['CID']                          = $data['1'];
            $result['LessonAssign']['SHEID']        = $data['2'];
            $result['LessonAssign']['V_STATUS']     = $data['3'];
            $result['mark']                         = $data['4'];
            $result['LessonAssign']['SSID']         = $data['5'];
            
            return $result;
        }
        return false;
    }
    
    public function getStudentResults($subjectId, $student) {
        $select = $this->getService('LessonAssign')->getSelect();
        $select -> from(array('si' => 'scheduleID'),
                        array(
                            'si.MID',
                            's.CID',
                            'si.SHEID',
                            'si.V_STATUS',
                            'si.SSID'
                        )
                )
                -> joinInner(array('s' => 'schedule'), 's.SHEID = si.SHEID')
                -> where('s.CID = ?', $subjectId)
                -> where('si.MID = ?', $student)
                -> where('isfree = ?', '0');
        $stmt = $select->query();
        $result = $stmt->fetchAll();
        
        return $result;
    }
    
    private function processData($data){
        $data = $this->mapData($data);
        $LessonService = $this->getService('Lesson');
        $LessonAssignService = $this->getService('LessonAssign');
        
        $SubjectService = $this->getService('Subject');
        $SubjectMarkService = $this->getService('SubjectMark');
        
        if( $LessonService->getLesson($data['LessonAssign']['SHEID']) &&
            $SubjectService->isStudent($data['CID'], $data['LessonAssign']['MID'])
        ){
            if( count($this->getStudentResults($data['CID'], $data['LessonAssign']['MID'])) ){
                $LessonAssignService->update($data['LessonAssign']);
            } else {
                $LessonAssignService->insert($data['LessonAssign']);
            }
            
            $StudentMark = $SubjectMarkService -> fetchAll($SubjectMarkService->quoteInto( 
                    array('mid = ? AND ', 'cid = ?'),
                    array($data['LessonAssign']['MID'], $data['CID'])
            ));
            
            if (count($StudentMark)){
                $SubjectMarkService->update(array(
                    'mid'   => $data['LessonAssign']['MID'],
                    'cid'   => $data['CID'],
                    'mark'  => $data['mark']
                ));
            } else {
                $SubjectMarkService->insert(array(
                    'mid'   => $data['LessonAssign']['MID'],
                    'cid'   => $data['CID'],
                    'mark'  => $data['mark']
                ));
            }
            return true;
        }
    }
    
    public function importResults($file_names){
        $path = Zend_Registry::get('config')->path->upload->tmp;
        $result = array();
        foreach ($file_names as $file_name) {
            $content = file_get_contents($path.'/'.$file_name);
            $content_arr = explode(";\r\n", $content);
            $result = array_merge($result, $content_arr);
        }
        $imported = 0;
        foreach($result as $value){
            if($value != ''){
                $value_arr = (explode(";", $value));
                if($this->processData($value_arr)){
                    $imported++;
                }
            }
        }
        return $imported;
    }
}