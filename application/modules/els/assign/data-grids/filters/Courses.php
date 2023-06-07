<?php

/**
 *
 */
class HM_Assign_DataGrid_Filter_Courses
{
    private $value;
    private $subjectField;

    public function __construct(HM_DataGrid $dataGrid, string $subjectField)
    {
        $this->value = array(
            'callback' => array(
                'function' => array($this, 'callback'),
                'params' => array($dataGrid)
            )
        );
        $this->subjectField = $subjectField;
    }

    static public function create($dataGrid, $subjectField)
    {
        $self = new self($dataGrid, $subjectField);
        return $self->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function callback($data)
    {
        $serviceContainer = $data[0]->getServiceContainer();
        $data['value'] = trim($data['value']);
        $search = trim($data['value']);
        $select = $data['select'];
        if (!$search) return $select;
        $s = $select->__toString();

        return  $select
            ->joinInner(['subjects'], $this->subjectField . '= subjects.subid', [])
            ->where(
            $serviceContainer->getService('Subject')->quoteInto(
                'LOWER(subjects.name) LIKE ?', '%' . $search . '%'
            )
        );
    }
}