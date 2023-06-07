<?php
class HM_Tag_Ref_AtSession_AtSessionService extends HM_Tag_Ref_RefService
{
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getFilterSelect()
     */
    public function getFilterSelect($search, Zend_Db_Select $select)
    {
        $search = trim( $search );

        if ( !$search ) return $select;

        return  $select->joinInner( array('ref' => 'tag_ref'),
                            	    'ref.item_id = p.MID AND ref.item_type = ' . $this->getUserType(),
                                    array())
                       ->joinInner( array('tg' => 'tag'),
                             		'ref.tag_id = tg.id',
                                    array())
                       ->where( $this->getService('User')->quoteInto('LOWER(tg.body) LIKE ?', '%' . $search . '%') );
    }
}