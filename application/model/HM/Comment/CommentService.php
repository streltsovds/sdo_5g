<?php
class HM_Comment_CommentService extends HM_Service_Abstract
{

    const EVENT_GROUP_NAME_PREFIX = 'BLOG_COMMENT_ADD';

    public function insert($data, $unsetNull = true) {
        $item = parent::insert($data, $unsetNull);
        return $item;
    }

    public function getRelatedUserList($id)
    {
        $db =  Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select();
        $result = array();

        $commentSelect = clone $select;
        $commentSelect->from(array('c' => 'comments'), array('subject_id' => 'c.subject_id'))
            ->where('c.id = ?', $id, 'INTERGER');
        $stmt = $commentSelect->query();
        $stmt->execute();
        $subjectRow = $stmt->fetchAll();
        $subjectId = $subjectRow[0]['subject_id'];

        if ($subjectId === null || intval($subjectId) == 0) {
            $select->from(array('c1' => 'comments'), array())
                ->join(array('c2' => 'comments'), 'c1.item_id = c2.item_id', array('CUID' => 'c2.user_id'))
                ->join(array('b' => 'blog'), 'b.id=c2.item_id', array('PUID' => 'b.created_by'))
                ->where('c1.id = ?', $id, 'INTEGER')
                ->group('CUID', 'PUID');
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $index => $item) {
                if ($index == 0) {
                    $result[] = intval($item['PUID']);
                }
                $result[] = intval($item['CUID']);
            }
            $result = array_unique($result);
        } else {
            $teachersSubselect = clone $select;
            $studentsSubselect = clone $select;
            $unionSelect = clone $select;
            $teachersSubselect->from(array('s' => 'subjects'), array())
                ->join(array('t' => 'Teachers'), 't.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 't.MID'));
            $studentsSubselect->from(array('s' => 'subjects'), array())
                ->join(array('st' => 'Students'), 'st.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 'st.MID'));
            $mainSelect = $unionSelect->union(array($teachersSubselect, $studentsSubselect))
                ->group('UserId');
            $stmt  = $mainSelect->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $item) {
                $result[] = intval($item['UserId']);
            }
        }
        return $result;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('комментарий plural', '%s комментарий', $count), $count);
    }
}
