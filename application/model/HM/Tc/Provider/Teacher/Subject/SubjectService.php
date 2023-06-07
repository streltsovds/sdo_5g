<?php
class HM_Tc_Provider_Teacher_Subject_SubjectService extends HM_Service_Abstract
{
    public function unAssign($teacherId = 0, $providerId = 0, $subjectId = 0)
    {
        $where = array(
            '1 = 1'
        );

        $params = array(
            0
        );

        if ($teacherId !== 0) {
            $where[] = ' AND teacher_id = ?';
            $params[] = $teacherId;
        }

        if ($providerId !== 0) {
            $where[] = ' AND provider_id = ?';
            $params[] = $providerId;
        }

        if ($subjectId !== 0) {
            $where[] = ' AND subject_id = ?';
            $params[] = $subjectId;
        }

        if (count($where) === 1) {
            return;
        }

        return $this->deleteBy($this->quoteInto($where, $params));

    }

    public function assign($teacherId, $subjectId)
    {
        static $cache = array();

        $sc = $this->getService('TcSubject')->getStudyCenter($subjectId, true);

        $providerId = $sc->provider_id;

        $existsRecord = $this->getOne($this->fetchAll(array(
            'teacher_id = ?' => $teacherId,
            'subject_id = ?' => $subjectId,
            'provider_id = ?' => $providerId?$providerId:'0'
        )));

        if ($existsRecord) {
            return true;
        }

        return $this->insert(array(
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'provider_id' => $providerId
        ));

    }

}