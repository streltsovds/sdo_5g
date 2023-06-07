<?php
class HM_Tc_Provider_Teacher_TeacherService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $data['created']    = HM_Date::now()->toString(HM_Date::SQL);
        $data['created_by'] = $userService->getCurrentUserId();

        return parent::insert($data, $unsetNull);

    }

    // if'ами в старом getListSource проблематично
    public function getListSource($options)
    {
        switch ($options['type']) {
            case HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER :
                return $this->_getScListSource($options);
            default :
                return $this->_getTcListSource($options);
        }
    }

    protected function _getTcListSource($options)
    {
        $default = array(
            'subjectId'   => 0,
            'providerId'  => 0,
            'type'        => HM_Tc_Provider_ProviderModel::TYPE_PROVIDER
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();

        $ratingSelect = clone $select;
        $ratingSelect
            ->from(
                array('s'   => 'subjects'),
                array(
                    's.subid',
                    'rating' => new Zend_Db_Expr('COUNT(gr.mid) * AVG(gr.effectivity) * AVG(sv.value)')
                ))
            ->joinInner(
                array('gr' => 'graduated'),
                'gr.CID = s.subid',
                array())
            ->joinLeft(
                array('f' => 'tc_feedbacks'),
                'gr.CID = f.subject_id AND gr.MID=f.user_id',
                array())
            ->joinLeft(
                array('sv' => 'scale_values'),
                'sv.value_id = f.mark',
                array())
            ->where('s.base=?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->group(array('s.subid'));

        $group = array(
            't.teacher_id',
            't.provider_id',
            't.contacts',
            't.name',
            't.created_by',
            'p.name'
        );
        if ($options['type'] == HM_Tc_Provider_ProviderModel::TYPE_PROVIDER) {
            $select
                ->from(array('t' => 'tc_provider_teachers'), array(
                    't.teacher_id',
                    'teacher_name' => 't.name',
                    't.provider_id',
                    't.contacts',
                    'created_by' => 't.created_by',
                    'provider_name' => 'p.name',
                    'courses' => new Zend_Db_Expr('GROUP_CONCAT(s.subid)'),
                    'rating'  => new Zend_Db_Expr('AVG(rt.rating)')
                ));
        } else {
            $select
                ->from(array('t' => 'tc_provider_teachers'), array(
                    't.teacher_id',
                    't.user_id',
                    'teacher_name' =>  new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pe.LastName, ' ') , pe.FirstName), ' '), pe.Patronymic)"),
                    't.provider_id',
                    't.contacts',
                    'created_by' => 't.created_by',
                    'provider_name' => 'p.name',
                    'courses' => new Zend_Db_Expr('GROUP_CONCAT(s.subid)'),
                    'rating'  => new Zend_Db_Expr('AVG(rt.rating)')
                ));
            $select->joinInner(array('pe' => 'People'), 't.user_id=pe.MID', array());
            $group = array_merge($group, array('t.user_id', 'pe.MID', 'pe.LastName', 'pe.FirstName', 'pe.Patronymic'));
        }

        $select->joinLeft(
                array('p' => 'tc_providers'),
                'p.provider_id = t.provider_id',
                array()
            )

            ->joinLeft(
                array('t2sr' => 'tc_provider_teachers2subjects'),
                't2sr.teacher_id = t.teacher_id',
                array()
            )

            ->joinLeft(
                array('rt' => $ratingSelect),
                't2sr.subject_id = rt.subid',
                array()
            )

            ->joinLeft(
                array('t2s' => 'tc_provider_teachers2subjects'),
                't2s.teacher_id = t.teacher_id',
                array()
            )

            ->joinLeft(
                array('s' => 'subjects'),
                's.subid = t2s.subject_id',
                array()
            )

            ->group($group);

        if ($options['subjectId']) {
            $select->where('s.subid = ?', $options['subjectId']);
        } else {
            $select->where('s.base != ? OR s.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
        }

        if ($options['providerId']) {
            $select->where('t.provider_id = ?', $options['providerId']);
        }
        $select->where('p.type = ?', $options['type']);

        return $select;
    }

    protected function _getScListSource($options)
    {
        $default = array(
            'subjectId'   => 0,
            'providerId'  => 0,
            'type'        => HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER,
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();

        $ratingSelect = clone $select;
        $ratingSelect
            ->from(
                array('s'   => 'subjects'),
                array(
                    's.subid',
                    'rating' => new Zend_Db_Expr('COUNT(gr.mid) * AVG(gr.effectivity) * AVG(sv.value)')
                ))
            ->joinInner(
                array('gr' => 'graduated'),
                'gr.CID = s.subid',
                array())
            ->joinLeft(
                array('f' => 'tc_feedbacks'),
                'gr.CID = f.subject_id AND gr.MID=f.user_id',
                array())
            ->joinLeft(
                array('sv' => 'scale_values'),
                'sv.value_id = f.mark',
                array())
            //->where('s.base=?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->group(array('s.subid'));

        $group = array();

        if ($options['subjectId']) {
            $select->where('sb.subid = ?', $options['subjectId']);
            $subject = $this->getOne($this->getService("TcSubject")->find($options['subjectId']));
        }
        if ($options['providerId']) {
            $select->where('p.provider_id = ?', $options['providerId']);
        }

        //if(!$subject || $subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $select
                ->from(array('t' => 'tc_provider_teachers'), array(
                    'MID' => 'pe.MID',
                    't.teacher_id',
                    't.user_id',
                    'teacher_name' =>  new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pe.LastName, ' ') , pe.FirstName), ' '), pe.Patronymic)"),
                    't.provider_id',
                    't.contacts',
                    'created_by' => 't.created_by',
                    'provider_name' => 'p.name',
                    'courses' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT sb.subid)'),
                    'rating'  => new Zend_Db_Expr('AVG(rt.rating)')
                ));
            $select->joinInner(
                array('pe' => 'People'),
                't.user_id=pe.MID',
                array()
            )
           ->joinLeft(
                array('p' => 'tc_providers'),
                'p.provider_id = t.provider_id',
                array()
            )
            ->joinLeft(
                array('t2sr' => 'tc_provider_teachers2subjects'),
                't2sr.teacher_id = t.teacher_id',
               array()
           )

            ->joinLeft(
                array('rt' => $ratingSelect),
                't2sr.subject_id = rt.subid',
                array()
            )

            ->joinLeft(
                array('t2s' => 'tc_provider_teachers2subjects'),
                't2s.teacher_id = t.teacher_id',
                array()
            )
            ->joinLeft(
                array('sb' => 'subjects'),
               'sb.subid = t2s.subject_id',
                array()
            )
        ;
            $group = array(
                'pe.MID',
                't.teacher_id',
                't.user_id',
                't.provider_id',
                't.contacts',
                't.name',
                't.created_by',
                'p.name',
                'pe.LastName',
                'pe.FirstName',
                'pe.Patronymic',
                //'sb.subid',
            );
       /* } else {
            $select->from(array('pe' => 'People'), array(
                't.MID',
                'teacher_id' => 't.MID',
                'user_id' => 't.MID',
                'teacher_name' =>  new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pe.LastName, ' ') , pe.FirstName), ' '), pe.Patronymic)"),
                'p.provider_id',
                'provider_name' => 'p.name',
                'rating'  => new Zend_Db_Expr('AVG(rt.rating)')
            ));
            $select->joinInner(array('t' => 'Teachers'), 't.MID=pe.MID', array());
            $group = array(
                't.PID',
                't.MID',
                'p.provider_id',
                //'tcpt.contacts',
                //'tcpt.name',
                //'tcpt.created_by',
                'p.name',
                'pe.LastName',
                'pe.FirstName',
                'pe.Patronymic'
            );

            $select->joinLeft(
                array('s' => 'subjects'),
                's.subid = t.CID AND s.provider_type = '.$options['type'],
                array('courses' => new Zend_Db_Expr('GROUP_CONCAT(s.subid)'))
            )->joinLeft(
                array('tcps' => 'tc_providers_subjects'),
                'tcps.subject_id = s.subid',
                array()
            )
            ->joinLeft(
                array('p' => 'tc_providers'),
                'p.provider_id = tcps.provider_id',
                array()
            );

            $select->joinLeft(
                array('rt' => $ratingSelect),
                's.subid = rt.subid',
                array()
            );
        }*/
        $select->group($group);
        return $select;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('преподаватель plural', '%s преподаватель', $count), $count);
    }


    public function getTeacherFiles($teacherId)
    {
        $result = array();
        $files  = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_TC_TEACHER, $teacherId);
        if ($files) {
            foreach ($files as $file) {
                $result[] = ' <a href="'.$file->getUrl().'">'.$file->getDisplayName().'</a>';
            }
        }

        return implode("<br \>\r\n", $result);
    }

    public function getScTeachersSelect()
    {
        $select = $this->getService('Teacher')->getSelect();
        $select->from(
            array('t' => 'Teachers'),
            array(
                'user_id' => 't.MID',
                'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'provider_name' => 'sc.name',
                'description' => 'sct.description',
                'contacts' => 'sct.contacts',
            )
        )->joinInner(
            array('p' => 'People'),
            'p.MID = t.MID',
            array()
        )->joinInner(
            array('sct' => 'tc_provider_teachers'),
            'sct.user_id = t.MID',
            array()
        )->joinLeft(
            array('sc' => 'tc_providers'),
            $this->quoteInto(
                'sc.provider_id = sct.provider_id AND sc.type = ?',
                HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER
            ),
            array()
        );

        return $select;
    }
}