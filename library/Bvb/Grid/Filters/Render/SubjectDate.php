<?php
class Bvb_Grid_Filters_Render_SubjectDate extends Bvb_Grid_Filters_Render_Date
{
    public function hasConditions()
    {
        return false;
    }

    public function buildQuery(array $filter)
    {
        $where = '';
        if (isset($filter['from'])) {
            $where .= '(';
            $where .= $this->getSelect()->getAdapter()->quoteInto($this->getFieldName().' >= ?', $this->transform($filter['from'], 'from'));
        }
        if (isset($filter['to'])) {
            if (isset($filter['from'])) {
                $where .= ' AND ';
            } else {
                $where .= '(';
            }

            $where .= $this->getSelect()->getAdapter()->quoteInto($this->getFieldName().' <= ?',$this->transform($filter['to'], 'to'));
        }
        if (strlen($where)) {
            $where .= ') OR ';
        }

        $where .= $this->getSelect()->getAdapter()->quoteInto('period = ?', HM_Subject_SubjectModel::PERIOD_FREE);

        if (in_array($this->getFieldName(), array('begin', 'end'))) {
            $where .= ' OR ';

            if (isset($filter['from'])) {
                $where .= '(';
                $where .= $this->getSelect()->getAdapter()->quoteInto($this->getFieldName().'_planned >= ?', $this->transform($filter['from'], 'from'));
            }
            if (isset($filter['to'])) {
                if (isset($filter['from'])) {
                    $where .= ' AND ';
                } else {
                    $where .= '(';
                }

                $where .= $this->getSelect()->getAdapter()->quoteInto($this->getFieldName().'_planned <= ?',$this->transform($filter['to'], 'to'));
            }

            if (strlen($where)) {
                $where .= ')';
            }

        }

        $this->getSelect()->where($where);
    }
}