<?php
class HM_Group_GroupService extends HM_Service_Abstract
{

    public function assignStudents($groupId, $students)
    {
        $group = $this->find($groupId)->current();
        if (!$group) {
            return;
        }

        $deletedStudents = array();
        $collection = $this->getService('GroupAssign')->fetchAll($this->quoteInto('gid = ?', $groupId));
        if (count($collection)) {
            $deletedStudents = $collection->getList('mid', 'mid');
        }

        $this->getService('GroupAssign')->deleteBy($this->quoteInto('gid = ?', $groupId));

        if (is_array($students) && count($students)) {
            foreach($students as $studentId) {
                if (isset($deletedStudents[$studentId])) {
                    unset($deletedStudents[$studentId]);
                }

                $this->getService('GroupAssign')->insert(
                    array(
                        'mid' => (int) $studentId,
                        'cid' => $group->cid,
                        'gid' => $groupId
                    )
                );
            }
        }

//#19443
        /*
        $specialityAssign = false;
        $collection = $this->getService('SpecialityGroup')->fetchAll($this->quoteInto('gid = ?', $groupId));
        if (count($collection)) {
            $specialityAssign = $collection->current();
        }
        if (count($deletedStudents) && $specialityAssign) {
            foreach($deletedStudents as $studentId) {
                $this->getService('Speciality')->unassignStudent(
                    $specialityAssign->trid,
                    $studentId,
                    $specialityAssign->level
                );
            }
        }
        */

    }


    public function assignStudent($group, $userId)
    {
        $this->getService('GroupAssign')->deleteBy($this->quoteInto(
            array('cid=?', ' and mid=?'),
            array($group->cid, $userId)
        ));

        $this->getService('GroupAssign')->insert(array(
            'cid' => $group->cid,
            'gid' => $group->gid,
            'mid' => $userId,
        ));
    }

    public function update($data, $unsetNull = true)
    {
        if(isset($data['students'])){ 
            $this->assignStudents($data['gid'], (isset($data['students']) ? $data['students'] : null));
        }
        
        if (isset($data['students'])) {
            unset($data['students']);
        }

        return parent::update($data);
    }

    public function delete($id)
    {
        $this->getService('GroupAssign')->deleteBy($this->quoteInto('gid = ?', $id));
//#19443
//        $this->getService('SpecialityGroup')->deleteBy($this->quoteInto('gid = ?', $id));
        return parent::delete($id);
    }
}