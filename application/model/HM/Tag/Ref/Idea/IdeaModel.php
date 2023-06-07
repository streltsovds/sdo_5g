<?php
class HM_Tag_Ref_Idea_IdeaModel extends HM_Tag_Ref_RefModel
{
    /* (non-PHPdoc)
     * @see HM_Model_Abstract::getServiceName()
     */
    public function getServiceName()
    {
        return 'TagRefIdea';
    }
    
    /* (non-PHPdoc)
     * @see HM_Tag_Ref_RefModel_Interface::getType()
     */
    public function getType()
    {
        return self::TYPE_IDEA;
    }
}