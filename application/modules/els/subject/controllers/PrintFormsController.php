<?php
class Subject_PrintFormsController extends HM_Controller_Action_Subject
{
    public function journalAction()
    {
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")))
            ->joinInner(array('s' => 'students'), 'p.MID = s.MID AND s.cid='.$this->_subjectId, array())
            ->joinLeft(array('j' => 'structure_of_organ'), 'j.MID = p.MID', array('job'=>'j.name', 'blank'=>new Zend_Db_Expr("null")));
        $destData1 = $select->query()->fetchAll();

        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")))
            ->joinInner(array('s' => 'graduated'), 'p.MID = s.MID AND s.cid='.$this->_subjectId, array())
            ->joinLeft(array('j' => 'structure_of_organ'), 'j.MID = p.MID', array('job'=>'j.name', 'blank'=>new Zend_Db_Expr("null")));
        $destData2 = $select->query()->fetchAll();

        $destData = array_merge($destData1, $destData2);

        $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, HM_PrintForm::FORM_STUDY_JOURNAL, array('subject'=>date('y'),'table_1'=>$destData,'table_2'=>$destData,'table_3'=>$destData), 'study_plan_'.$this->_getParam('session_id'));
    }

    public function protocolAction()
    {
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"), 'p.MID', 'p.Login'))
            ->joinInner(array('s' => 'graduated'), 'p.MID = s.MID AND s.cid='.$this->_subjectId, array())
            ->joinLeft(array('j' => 'structure_of_organ'), 'j.MID = p.MID', array('job'=>'j.name', 'blank'=>new Zend_Db_Expr("null")))
            ->joinLeft(array('oj' => 'structure_of_organ'), 'j.owner_soid = oj.soid', array('dep'=>'oj.name'))
            ->joinLeft(array('m' => 'courses_marks'), '(m.cid = s.CID AND m.mid = s.MID)', array('mark'=>'m.mark'));

        $graduatedIds = $this->_getParam('postMassIds_grid');

        if($graduatedIds) {
            $graduatedIdsList = explode(',', $graduatedIds);
            $select->where('s.SID in (?)', $graduatedIdsList);
        }

        $destData = $select->query()->fetchAll();

        // меняем числовое значение оценки на сдал/не сдал.
        // Про CASE WHEN THEN ELSE END знаю, но что-то упорно ломало запрос,
        // чтобы не залипнуть надолго решил пока так...
        // TODO: разобраться и переделать в виде запроса к БД
        foreach ($destData as $number => $result) {
            $item = array();
            foreach ($result as $key => $value) {
                $item[$key] = ($key != 'mark') ? $value : ($value != 1 ? 'не сдал' : 'сдал');
            }
            $destData[$number] = $item;
        }

        $template       = HM_PrintForm::FORM_STUDY_PROTOCOL;
        $data           = array('table_1' => $destData);
        $outputFileName = 'study_protocol';
        $this->getService('PrintForm')->makePrintForm(
            HM_PrintForm::TYPE_WORD,
            $template,
            $data,
            $outputFileName
        );
    }
}
