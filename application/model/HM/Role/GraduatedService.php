<?php
class HM_Role_GraduatedService extends HM_Service_Abstract
{
    public function insert($data, $customEnd = false)
    {
        if ($customEnd) {
            $data['created'] = $this->getDateTime();
        } else {
            $data['end']     =
            $data['created'] = $this->getDateTime();
        }

        $data['progress'] = intval($this->getService('Subject')->getUserProgress($data['CID'],$data['MID']));
        $assign = parent::insert($data);
        if ($assign) {

            // удаляем из слушеателей (на всякий случай, если еще не удалён)
            $this->getService('Student')->deleteBy(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?'),
                    array($assign->MID, $assign->CID)
                )
            );

            // Отправка сообщения
            $subjectMark = $this->getOne($this->getService('SubjectMark')->fetchAll(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?'),
                    array($assign->MID, $assign->CID)
                )
            ));

            $mark = '-';

            if ($subjectMark) {
                $mark = $subjectMark->mark;
            }

            $certificateId = $assign->certificate_id;
            $certificatePath = Zend_Registry::get('config')->path->upload->certificates .  $certificateId . ".pdf";

            $view = Zend_Registry::get('view');
            $certificateLink = ( file_exists($certificatePath) ) ? '<a href="' . $view->serverUrl(
                    $view->url(array('action'=>'certificate',
                            'controller' => 'get',
                            'module' =>'file',
                            'certificate_id' => $certificateId)
                    )
                ) . '" > Сертификат </a>': '';

            $messenger = $this->getService('Messenger');
            $messenger->setOptions(
                HM_Messenger::TEMPLATE_GRADUATED,
                array(
                    'subject_id' => $assign->CID,
                    'grade' => $mark,
                    'role' => _('Прошедший обучение'),
                    'certificate_link' => $certificateLink
                ),
                'subject',
                $assign->CID
            );
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $assign->MID);
        }
        return $assign;
    }

    public function isUserExists($subjectId, $userId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $subjectId, 'MID = ?' => $userId));
        return count($collection);
    }

    /*
     *  Для прошедших обучение доступность курса
     *  определяется галочкой "Нестрогое ограничение"
     */
    public function isSubjectUnccessible($graduated, $subject)
    {
        return $subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT
            ? false
            : _('Курс завершен и установлено строгое ограничение доступа к курсу');
    }

    public function getSubjectDates($graduated, $subject)
    {
        $return = array();

        if ($graduated->end) {
            $end = new HM_Date($graduated->end);
            $return['end'] = $end->toString(Zend_Date::DATES);
        }

        return $return;
    }

    public function getCachedSubjectProgramm($graduated, $subject)
    {
        return '';
    }


    public function getCachedSubjectMark($graduated, $subject)
    {
        $subjectId = $graduated->CID;
        if ($subjectId) {

            if ($mark = $graduated->getCachedValue('subjectId2Mark', $subjectId)) {
                return $mark;
            }
        }
        return false;
    }
}