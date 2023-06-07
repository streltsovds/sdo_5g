<?php
class Standard_AjaxController extends HM_Controller_Action
{
    public function treeAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();

        $profileId = $this->_getParam('profile_id', 0);

        $existFunctions = $this->getService('AtProfileFunction')->fetchAll(array('profile_id = ?'=>$profileId))->getList('function_id');
        $collection = $this->getService('AtStandard')->fetchAllDependenceJoinInner('AtStandardFunction');

        foreach($collection as $standard) {
            echo "-{$standard->standard_id}={$standard->name}\n";
            foreach($standard->function as $function) {
                $existSign = isset($existFunctions[$function->function_id]) ? '+' : '';
                echo "{$function->function_id}{$existSign}=-{$function->name}\n";
            }
        }
    }
}