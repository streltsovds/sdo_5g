<?php
class HM_Tag_Ref_Poll_PollService extends HM_Tag_Ref_RefService
{
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemViewAction()
     */
    public function getItemViewAction($itemId)
    {
        if ( !$this->getService('Acl')->isCurrentAllowed('mca:question:list:quiz') ) return null;
        return array('module' => 'question',
        		     'controller' => 'list',
                     'action' => 'quiz',
                     'quiz_id' => $itemId);
    }
    
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemTitle()
     */
    public function getItemTitle($itemId)
    {
        $rService = $this->getService('Poll');
        $item = $rService->getOne($rService->find($itemId));
        return ( $item )? $item->title : _('Нет');
    }
    
	
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getItemDescription()
     */
    public function getItemDescription($itemId)
    {
        $rService = $this->getService('Poll');
        $item = $rService->getOne($rService->find($itemId));
        return ( $item )? $item->description : '';
    }
    
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getFilterSelect()
     */
    public function getFilterSelect($search, Zend_Db_Select $select)
    {
        $search = trim( $search );
        
        if ( !$search ) return $select;
        
        return  $select->joinInner( array('ref' => 'tag_ref'),
                            	    'ref.item_id = t.quiz_id AND ref.item_type = ' . $this->getPollType(),
                                    array())
                       ->joinInner( array('tg' => 'tag'),
                             		'ref.tag_id = tg.id',
                                    array())
                       ->where( $this->getService('Resource')->quoteInto('LOWER(tg.body) LIKE ?', '%' . $search . '%') );
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefService_Interface::getIcon()
     */
    public function getIcon()
    {
        return array('src' =>'/images/events/4g/64x/poll.png','title' => _('Опрос'));
    }
}