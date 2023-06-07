<?php
class HM_Quest_Question_Type_ImageMapModel extends HM_Quest_Question_Type_MultipleModel
{
    protected function _serializeData(&$data)
    {
        $data['data'] = serialize(array(
            'show_variants' => $data['show_variants']
        ));

        unset($data['show_variants']);

    }

    public function emptyVariantsAllowed()
    {
        return true;
    }
}