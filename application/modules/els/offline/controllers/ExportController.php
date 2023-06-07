<?php
class Offline_ExportController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    
    public function indexAction()
    {
    }

    public function downloadAction() 
    {
        $data = array('mid' => $this->getService('User')->getCurrentUserId(), 'marks' => array(), 'loguser' => array(), 'logseance' => array(), 'scorm_tracklog' => array(), 'testcount' => array());
        
        $fields = array('V_DONE', 'V_STATUS', 'MID', 'SSID', 'SHEID', 'test_date', 'updated');
        $collection = $this->getService('LessonAssign')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));
        foreach($collection as $value) {
            $tmp = array();
            foreach ($fields as $field) {
                $tmp[$field] = $value->$field;
            }
            $data['marks'][] = $tmp;
        }
        
        $fields = array('stid', 'mid', 'cid', 'tid', 'balmax', 'balmin', 'balmax2', 'balmin2', 'bal', 'questdone', 'questall', 'qty', 'free', 'skip', 'start', 'stop', 'fulltime', 'moder', 'needmoder', 'status', 'moderby', 'modertime', 'teachertest', 'log', 'sheid');
        $collection = $this->getService('TestResult')->fetchAll(array('mid = ?' => $this->getService('User')->getCurrentUserId()));
        foreach($collection as $value) {
            $tmp = array();
            foreach ($fields as $field) {
                $tmp[$field] = $value->$field;
            }
            $data['loguser'][] = $tmp;
        }

        $fields = array('stid', 'mid', 'cid', 'tid', 'kod', 'number', 'time', 'bal', 'balmax', 'balmin', 'good', 'vopros', 'otvet', 'attach', 'filename', 'text', 'sheid', 'comments', 'review', 'review_filename', 'qtema');
        $collection = $this->getService('QuestionResult')->fetchAll(array('mid = ?' => $this->getService('User')->getCurrentUserId()));
        foreach($collection as $value) {
            $tmp = array();
            foreach ($fields as $field) {
                $tmp[$field] = $value->$field;
            }
            $data['logseance'][] = $tmp;
        }
        
        $fields = array('trackID', 'mid', 'cid', 'ModID', 'McID', 'lesson_id', 'trackdata', 'stop', 'start', 'score', 'scoremax', 'scoremin', 'status');
        $collection = $this->getService('ScormTrack')->fetchAll(array('mid = ?' => $this->getService('User')->getCurrentUserId()));
        foreach($collection as $value) {
            $tmp = array();
            foreach ($fields as $field) {
                $tmp[$field] = $value->$field;
            }
            $data['scorm_tracklog'][] = $tmp;
        }
        
        $fields = array('mid', 'cid', 'tid', 'qty', 'last', 'lesson_id');
        $collection = $this->getService('TestAttempt')->fetchAll(array('mid = ?' => $this->getService('User')->getCurrentUserId()));
        foreach($collection as $value) {
            $tmp = array();
            foreach ($fields as $field) {
                $tmp[$field] = $value->$field;
            }
            $data['testcount'][] = $tmp;
        }
        
        $str = json_encode($data);
        $str = offline_crypt($str);
        $this->_helper->SendFile->sendData($str, 'application/unknown', 'offline_' . date('Ymd_His') . '.dat');
        exit();
        //$this->_flashMessenger->addMessage(array('message' => _('Нет данных для экспорта'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        //$this->_redirectToIndex();
    }

}

function offline_crypt($text, $decrypt = false) {
    $key = "#42^&bjsopa1!";

    if ($decrypt) {
        return base64_decode($text);
    } else {
        return base64_encode($text);
    }

    /* Open the cipher */
    $td = mcrypt_module_open('blowfish', '', 'ecb', '');

    /* Create the IV and determine the keysize length, use MCRYPT_RAND
     * on Windows instead */
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RANDOM);
    $ks = mcrypt_enc_get_key_size($td);

    /* Create key */
    $key = substr(md5($key), 0, $ks);

    /* Intialize encryption */
    mcrypt_generic_init($td, $key, $iv);

    if (!$decrypt) {
        $text = mcrypt_generic($td, $text);        
    }
    else {
        $text = mdecrypt_generic($td, $text);
        $text = trim($text);
    }
    
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    
    return $text;
}