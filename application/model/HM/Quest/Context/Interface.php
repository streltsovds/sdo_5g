<?php
interface HM_Quest_Context_Interface
{
    
    /**
     * @return Array ('context_type' => $this->type, 'context_event_id' => $this->id)
     */
    public function getQuestContext();

    public function getRedirectUrl();
}
