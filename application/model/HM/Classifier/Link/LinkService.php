<?php
class HM_Classifier_Link_LinkService extends HM_Service_Abstract
{
    public function getRelevantSubjectsForUser($user_id, $classifier_type = null)
    {
	   	$select = $this->getSelect();
		$select->from(
            array('clu' => 'classifiers_links'),
            array('subject_id' => 's.subid')
        )
		->join(
            array('cls' => 'classifiers_links'),
            'cls.classifier_id = clu.classifier_id AND cls.type = '.HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array(/*'count' => 'COUNT(cls.classifier_id)'*/)
        );

		if(!is_null($classifier_type)) {
            $classifiersJoinCondition = 'c.classifier_id = clu.classifier_id AND c.type = '.$classifier_type; // AND c.level != 0
        } else {
            $classifiersJoinCondition = 'c.classifier_id = clu.classifier_id';
        }

		$select->join(
            array('c' => 'classifiers'),
            $classifiersJoinCondition,
            array()
        )
		->join(
            array('s' => 'subjects'),
            's.subid = cls.item_id AND (
                s.reg_type = '.HM_Subject_SubjectModel::REGTYPE_FREE.' OR 
                s.reg_type = '.HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN.'
            )',
            array()
        )
		->joinLeft(
            array('st' => 'Students'),
            'st.CID = s.subid AND st.MID = '.$user_id,
            array('registeged' => 'st.registered')
        )
		->group(array('s.subid', 'st.registered'))
		->where('registered IS NULL')
		->where('s.type = ?', HM_Subject_SubjectModel::TYPE_DISTANCE)
		->where('clu.type = ?', HM_Classifier_Link_LinkModel::TYPE_PEOPLE)
		->where('clu.item_id = ?', $user_id);
		
		$tmp = $select->query()->fetchAll();
		$tmp = (is_array($tmp)) ? $tmp : array();
		
		$relevant_subjects = array();
		foreach ($tmp as $value) {
            $relevant_subjects[] = $value['subject_id'];
		}
    
		return $relevant_subjects;
    }

    public function getRelevantSubjectsForUserSubjects($userId, $excludedSubjectIds = [])
    {
        if(!count($excludedSubjectIds)) $excludedSubjectIds = [0];

        $select = $this->getSelect()->distinct();
        $select->from(
            array('cls' => 'classifiers_links'),
            array('subject_id' => 's.subid')
        )
        ->join(
            ['su' => 'subjects_users'],
            $this->quoteInto(
                [
                    'su.subject_id=cls.item_id and su.user_id = ?',
                    ' and subject_id not in (?)',
                ],
                [
                    $userId,
                    $excludedSubjectIds
                ]
            ),
            []
        )
        ->join(
            array('c' => 'classifiers'),
            'c.classifier_id = cls.classifier_id',
            array()
        )
        ->join(
            array('clss' => 'classifiers_links'),
            '
                clss.classifier_id = cls.classifier_id AND
                (clss.item_id <> cls.item_id) AND 
                cls.type = '.HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array(/*'count' => 'COUNT(cls.classifier_id)'*/)
        )
        ->join(
            array('s' => 'subjects'),
            's.subid = clss.item_id AND (
                s.reg_type = '.HM_Subject_SubjectModel::REGTYPE_FREE.' OR 
                s.reg_type = '.HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN.'
            )',
            array()
        )
        ->where('cls.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT)
        ->where('s.type = ?', HM_Subject_SubjectModel::TYPE_DISTANCE)
        ;

        $subjectRows = $select->query()->fetchAll();
        $result = [];

        foreach ($subjectRows as $subjectRow) {
            $result[] = $subjectRow['subject_id'];
        }

        return $result;
    }

    /**
     * with rewrite all classifiers of this $item_id and $type!
     * @param  $item_id
     * @param  $type
     * @param  $classifiers
     * @return
     */
    public function setClassifiers($item_id, $type, $classifiers)
    {
        $this->getService('ClassifierLink')->deleteBy($this->getService('ClassifierLink')->quoteInto(
            array('item_id = ?', 'AND type = ?'),
            array($item_id, $type)
        ));

        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                $res = $this->getService('ClassifierLink')->insert(array(
                                                                        'item_id' => $item_id,
                                                                        'classifier_id' => $classifierId,
                                                                        'type' => $type
                                                                   ));
            }
        }
        return $res;

    }

    public function getClassifiers($itemId, $typeId)
    {
        $return = array();
        $links = $this->fetchAllDependenceJoinInner('Classifier', $this->quoteInto(
            array('self.item_id = ?', ' AND Classifier.type = ?'), // не работает
            array($itemId, $typeId)
        ));
        if (count($links)) {
            foreach ($links as $link) {
                if (count($link->classifiers)) {
                    foreach ($link->classifiers as $classifier) {
                        if ($classifier->type != $typeId) continue;
                        $return[] = $classifier;
                    }
                }
            }
        }
        return $return;
    }
}