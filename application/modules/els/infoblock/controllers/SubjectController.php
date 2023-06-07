<?php

class Infoblock_SubjectController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();
        header("Pragma: cache");

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
    }

    /**
     * @method Прогресс прохождения очных курсов (тренингов)
     */
    public function progressAction()
    {
        if ($this->isAjaxRequest() && $this->_request->isGet()) {
            $soid = (int) $this->_getParam('soid', 0);
            $profileId = (int) $this->_getParam('job_profile', 0);

            Zend_Registry::get('session_namespace_default')->subj_prgrs->soid = $soid;
            Zend_Registry::get('session_namespace_default')->subj_prgrs->profileId = $profileId;

            $response = $this->getProgressStats(HM_Subject_SubjectModel::TYPE_FULLTIME, $soid, $profileId);

            return $this->responseJson($response);
        }
    }

    /**
     * @method Прогресс прохождения дистанционных курсов
     */
    public function distancePogressAction()
    {
        if ($this->isAjaxRequest() && $this->_request->isGet()) {
            $soid = (int) $this->_getParam('soid', 0);
            $profileId = (int) $this->_getParam('job_profile', 0);

            Zend_Registry::get('session_namespace_default')->dstnc_subj_prgrs->soid = $soid;
            Zend_Registry::get('session_namespace_default')->dstnc_subj_prgrs->profileId = $profileId;

            $response = $this->getProgressStats(HM_Subject_SubjectModel::TYPE_DISTANCE, $soid, $profileId);

            return $this->responseJson($response);
        }
    }

    private function getProgressStats($subjectType, $soid, $profileId)
    {
        $caseWhenThen = new Zend_Db_Expr("CASE WHEN s.base_id != 0 THEN s.base_id ELSE s.subid END");

        $subselectSubjectsStudents = Zend_Db_Table_Abstract::getDefaultAdapter()->select()
            ->from(
                array('s' => 'subjects'),
                array(
                    'base_subid' => $caseWhenThen,
                    'su.user_id',
                )
            )
            ->joinInner(
                array('su' => 'subjects_users'),
                's.subid = su.subject_id',
                array()
            )
            ->joinInner(
                array('so' => 'structure_of_organ'),
                'su.user_id = so.mid',
                array()
            )
            ->where($this->quoteInto('s.is_fulltime = ?', $subjectType))
            ->where($this->quoteInto('su.status IN (?)', array(HM_Subject_User_UserModel::SUBJECT_USER_STUDENT, HM_Subject_User_UserModel::SUBJECT_USER_GRADUATED)))
            ->group(array('su.user_id', $caseWhenThen));

        if ($soid > 0) {
            $subselectSubjectsStudents
                ->joinInner(
                    array('upper_so' => 'structure_of_organ'),
                    'so.lft > upper_so.lft AND so.rgt < upper_so.rgt',
                    array()
                )
                ->where($this->quoteInto('upper_so.soid = ?', $soid));
        }

        if ($profileId > 0) {
            $subselectSubjectsStudents
                ->joinInner(
                    array('ap' => 'at_profiles'),
                    'so.profile_id = ap.profile_id',
                    array()
                )
                ->where($this->quoteInto('ap.profile_id = ?', $profileId));
        }

        $selectSubjectsStudents = Zend_Db_Table_Abstract::getDefaultAdapter()->select()
            ->from(
                array('stdnts' => $subselectSubjectsStudents),
                array(
                    'subid' => 'stdnts.base_subid',
                    'title' => 'sbjct.name',
                    'all'   => new Zend_Db_Expr('COUNT(stdnts.user_id)')
                )
            )
            ->joinInner(
                array('sbjct' => 'subjects'),
                'stdnts.base_subid = sbjct.subid',
                array()
            )
            ->group(array('base_subid', 'sbjct.name'));

        // Курсы и количество работников на каждом курсе
        $subjectsStudents = $selectSubjectsStudents->query()->fetchAll();

        $subselectGraduated = Zend_Db_Table_Abstract::getDefaultAdapter()->select()
            ->from(
                array('s' => 'subjects'),
                array('base_subid' => $caseWhenThen, 'g.MID')
            )
            ->joinInner(
                array('g' => 'graduated'),
                's.subid = g.CID',
                array()
            )
            ->joinInner(
                array('so' => 'structure_of_organ'),
                'g.MID = so.mid',
                array()
            )
            ->where($this->quoteInto('s.is_fulltime = ?', $subjectType))
            ->group(array(
                'g.MID', $caseWhenThen
            ));

        if ($soid > 0) {
            $subselectGraduated
                ->joinInner(
                    array('upper_so' => 'structure_of_organ'),
                    'so.lft > upper_so.lft AND so.rgt < upper_so.rgt',
                    array()
                )
                ->where($this->quoteInto('upper_so.soid = ?', $soid));
        }

        if ($profileId > 0) {
            $subselectGraduated
                ->joinInner(
                    array('ap' => 'at_profiles'),
                    'so.profile_id = ap.profile_id',
                    array()
                )
                ->where($this->quoteInto('ap.profile_id = ?', $profileId));
        }

        $selectGraduatedStudents = Zend_Db_Table_Abstract::getDefaultAdapter()->select()
            ->from(
                array('stdnts' => $subselectGraduated),
                array(
                    'cid'   => 'stdnts.base_subid',
                    'count' => new Zend_Db_Expr('COUNT(stdnts.MID)')
                )
            )
            ->group('base_subid');

        // Работники, прошедшие курсы
        $graduatedStudents = $selectGraduatedStudents->query()->fetchAll();

        $graduatedFormatted = array_column($graduatedStudents, 'count', 'cid');
        foreach ($subjectsStudents as &$subject) {
            $subject['count'] = isset($graduatedFormatted[$subject['subid']]) ? $graduatedFormatted[$subject['subid']] : 0;

            // Потому что на "ноль" делить нельзя!
            $dividerNumber = $subject['all'] === 0 ? 1 : $subject['all'];

            $subject['percent'] = round($subject['count'] / $dividerNumber * 100);
            $subject['title'] = $subject['title'];
        }
        unset($subject);
        usort($subjectsStudents, function ($a, $b) {
            return $b['percent'] - $a['percent'];
        });

        $return = array();
        foreach ($subjectsStudents as $value) {
            $chartData = new stdClass();
            $chartData->{'0'} = $value['percent'];
            $chartData->title = $value['title'];
            $return['chartData'][] = $chartData;

            $tooltipData = new stdClass();
            $tooltipData->count = $value['count'];
            $tooltipData->all = $value['all'];
            $return['tooltipData'][] = $tooltipData;
        }

        return $return;
    }
}
