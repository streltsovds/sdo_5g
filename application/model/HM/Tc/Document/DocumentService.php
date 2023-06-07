<?php
class HM_Tc_Document_DocumentService extends HM_Service_Abstract
{

    public function getListSource($subjectId)
    {
        $select = $this->getSelect();
        $select->from(array('tcdocs' => 'tc_document'), array(
            'tcdocs.document_id',
//            'tcpr.provider_id',
            'document_name' => 'tcdocs.name',
            'tcdocs.add_date',
//            'provider_name' => 'tcp.name',
            'tcdocs.type',
        ));
        $select->where('tcdocs.subject_id=?', $subjectId);

        return $select;

    }


    public function delete($id)
    {
        $doc = $this->getOne($this->find($id));

        $fullname = HM_Tc_Document_DocumentModel::uploadPath($doc->subject_id).$doc->filename;
        unlink($fullname);
        parent::delete($id);
    }




    
}