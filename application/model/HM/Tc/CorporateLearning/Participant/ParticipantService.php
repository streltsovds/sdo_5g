<?php
class HM_Tc_CorporateLearning_Participant_ParticipantService extends HM_Service_Abstract
{
    protected function _getOrgstructureNameDbSelect()
    {
        /** @var HM_Orgstructure_OrgstructureService $orgService */
        $orgService = $this->getService('Orgstructure');

        $select = $orgService->getSelect();

        $name = "(".
                    "CASE WHEN ".
                        "op1.soid IS NULL ".
                    "THEN ".
                        "(".
                            "CASE WHEN ".
                                "op2.soid IS NULL ".
                            "THEN ".
                                "o.name ".
                            "ELSE ".
                                "CONCAT(op2.name, CONCAT(' / ', o.name)) ".
                            "END".
                        ") ".

                    "ELSE ".
                        "(".
                            "CASE WHEN ".
                                "op2.soid IS NULL ".
                            "THEN ".
                                "CONCAT(op1.name, CONCAT(' / ', o.name)) ".
                            "ELSE ".
                                "CONCAT(op2.name, CONCAT(' / ', CONCAT(op1.name, CONCAT(' / ', o.name)))) ".
                            "END".
                        ") ".
                    "END".
                ")";

        $select
            ->from(array('o' => 'structure_of_organ'), array(
                'soid' => 'o.soid',
                'name' => new Zend_Db_Expr($name)
            ))
            ->joinLeft(array('op1' => 'structure_of_organ'), 'op1.lft < o.lft AND op1.lft < o.rgt AND op1.rgt > o.rgt AND op1.rgt > o.lft AND op1.level = 1', array())
            ->joinLeft(array('op2' => 'structure_of_organ'), 'op2.lft < o.lft AND op2.lft < o.rgt AND op2.rgt > o.rgt AND op2.rgt > o.lft AND op2.level = 0', array());

        return $select;

    }

    public function getParticipantName($participantId)
    {
        $select = $this->_getOrgstructureNameDbSelect();
        $select->where('o.soid = ?', $participantId);

        $result = $select->query()->fetch();

        if (!$result) {
            return '';
        }

        return $result['name'];

    }

    public function findInOrgstructure($search)
    {
        $result = array();

        if (strlen($search) < 3) {
            return $result;
        }

        $search = '%'.$search.'%';

        $select = $this->_getOrgstructureNameDbSelect();

        $select
            ->where("o.lft + 1 < o.rgt")
            ->where("$name LIKE ?", $search)
            ->limit(20);

        $rows = $select->query()->fetchAll();

        foreach ($rows as $row) {
            $result[] = array(
                'key' => $row['name'],
                'value' => $row['soid']
            );
        }

        return $result;

    }

    public function getParticipants($corporateLearningId = 0) {
        $select = $this->getSelect();
        $select->from(
            array('p' => 'tc_corporate_learning_participant'),
            array('so.name', 'p.cost')
        );
        $select->joinLeft(
            array('so' => 'structure_of_organ'),
            'so.soid = p.participant_id',
            array()
        );
        $select->where('corporate_learning_id = ?', $corporateLearningId);
        
        $stmt = $select->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows;
    }
    
    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('участник plural', '%s участник', $count), $count);
    }
    
}