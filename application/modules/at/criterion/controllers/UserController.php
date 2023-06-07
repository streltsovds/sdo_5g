<?php
class Criterion_UserController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();
    }

   public function staffAction()
   {

       $position = $this->getService('Orgstructure')->fetchAll(
           $this->getService('Orgstructure')->quoteInto(
               array (' mid = ? AND ', ' blocked = ? '),
               array($this->getService('User')->getCurrentUserId(), 0)
           ))
          ->current();

       $return = array();
       if ($position->is_manager) {

           $directDescendants = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto(array(
               'owner_soid = ? AND ',
               'type = ? AND ',
               'is_manager = ? AND ',
               'blocked = ?'
           ), array(
               $position->owner_soid,
               HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
               HM_Orgstructure_Position_PositionModel::ROLE_EMPLOYEE,
               0
           )));
           foreach ($directDescendants as $position) {
               $return[] = $position;
           }

           $childDepartments = $this->getService('Orgstructure')->fetchAllDependenceJoinInner('Descendant', $this->getService('Orgstructure')->quoteInto(array(
               'self.owner_soid = ? AND ',
               'self.type = ? AND ',
               'Descendant.type = ? AND ', // не работает
               'Descendant.is_manager = ?', // не работает
           ), array(
               $position->owner_soid,
               HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
               HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
               HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
           )));
           foreach ($childDepartments as $department) {
               foreach ($department->descendants as $position) {

                   if ($position->type != HM_Orgstructure_OrgstructureModel::TYPE_POSITION) continue;
                   if ($position->is_manager != HM_Orgstructure_Position_PositionModel::ROLE_MANAGER) continue;
                   if ($position->blocked == 1) continue;

                   $return[] = $position;
               }
           }
       }
       $this->prepareData($return);
   }


    public function orgstructureAction()
    {
        $soids = explode(',', $this->_getParam('postMassIds_grid', ''));
        $soids = $this->getService('Orgstructure')->getDescendansForMultipleSoids($soids);

        if (!empty($soids)) {
            $positions = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto('soid IN (?) ', $soids));
        } else {
            $positions = array();
        }

        $this->prepareData($positions);
    }


    function prepareData($positions) {
        $people = array();

        $peopleNames = array();
        $soNames = array();
        $soParents = array();

        foreach ($positions as $position) {
            if (!$position->mid) {
                continue;
            }

            $parent_so = $this->getService('Orgstructure')->fetchAll(array('soid = ?' => $position->owner_soid))
                ->current();

            $people[] = array(
                'id' => $position->mid,
                'profile_id' => $position->profile_id,
                'so_name' => $position->name,
                'so_parent' => $parent_so->name,
                'position_id' => $position->soid,
            );
            $soNames[] = $position->name;
            $soParents[] = $parent_so->name;
        }

        $criteriaCache = array();
        $criteriaTestCache = array();

        if (count($collection = $this->getService('AtCriterion')->fetchAll())) {
            $criteriaCache = $collection->getList('criterion_id', 'name');
        }
        if (count($collection = $this->getService('AtCriterionTest')->fetchAll())) {
            $criteriaTestCache = $collection->getList('criterion_id', 'name');
        }

        $scaleId = $this->getService('Option')->getOption('competenceScaleId');

        $competencesKeys = array();
        foreach($people as $key => $person) {

            $man = $this->getService('User')->find($person['id'])->current();
            if (!$man->MID) {
                unset($people[$key]);
                continue;
            }
            $name = $this->getService('User')->find($person['id'])->current()->getName();

            $people[$key]['user_name'] = $name;
            $peopleNames[] = $name;

            $competences = array();
            if (count($collection = $this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue', 'Category'), $person['profile_id']))) {
                $profile = $collection->current();
                if (count($profile->criteriaValues)) {
                    foreach ($profile->criteriaValues as $criterionValue) {
                        if (
                            $criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE
                            &&
                            $criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL
                        ) continue;
                        //$value = HM_Scale_Converter::getInstance()->id2value($criterionValue->value_id, $scaleId);
                        $competences[$criterionValue->criterion_id] = $criterionValue->value; //$value != HM_Scale_Value_ValueModel::VALUE_NA ? $value : '';
                    }
                    $people[$key]['competences'] = $competences;
                }
            }

            $sessionUsers = $this->getService('AtSessionUser')->fetchAllDependence(array('Session', 'CriterionValue'), array('user_id = ?' => $person['id']), 'session_user_id ASC');

            $sessions = $criteria = $results = array();
            if (count($sessionUsers)) {
                $sessionUser = $sessionUsers[count($sessionUsers)-1];
                if (count($sessionUser->session) && ($sessionUser->status == HM_At_Session_User_UserModel::STATUS_COMPLETED)) {
                    $session = $sessionUser->session->current();
                    if (in_array($session->programm_type,
                        array(
                            HM_Programm_ProgrammModel::TYPE_RECRUIT,
                            HM_Programm_ProgrammModel::TYPE_ASSESSMENT,
                            HM_Programm_ProgrammModel::TYPE_SUBJECT)
                        )
                    ) {
                        $sessions[$session->session_id] = $session->name;
                        if (count($sessionUser->criterionValues)) {
                            foreach ($sessionUser->criterionValues as $criterionValue) {
                                if ($criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE
                                    &&
                                    $criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL
                                ) continue;
                                $criteria[$criterionValue->criterion_id] = $criteriaCache[$criterionValue->criterion_id];
                                $results[$criterionValue->criterion_id] = $criterionValue->value;
                            }
                        }
                    }
                }
            }
            $people[$key]['results'] = $results;
            $competencesKeys = array_merge($competencesKeys, array_keys($results));
        }
        $uniqueCriteria = array_unique($competencesKeys);

        foreach ($criteriaCache as $criteriaKey => $criteriaValue) {
            if (!in_array($criteriaKey, $uniqueCriteria)) {
                unset($criteriaCache[$criteriaKey]);
            }
        }
        foreach ($criteriaTestCache as $criteriaKey => $criteriaValue) {
            if (!in_array($criteriaKey, $uniqueCriteria)) {
                unset($criteriaTestCache[$criteriaKey]);
            }
        }
        $this->view->criterias = array($criteriaCache, $criteriaTestCache);
        $this->view->people = $people;

        $filters = array();
        $filters['names'] = array_unique($peopleNames);
        $filters['so_names'] = array_unique($soNames);
        $filters['so_positions'] = array_unique($soParents);

        $this->view->filters = $filters;

    }



    function excelAction()
    {
        $soids = explode(',', $this->_getParam('positionIds', ''));


        if (!empty($soids)) {
            $positions = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto('soid IN (?) ', $soids));
        } else {
            $positions = array();
        }

        $this->prepareData($positions);

        $title = _('Лист 1');

        $titles = array();
        $titles[] = "ФИО";
        $titles[] = "Должность";
        $titles[] = "Подразделение";

        foreach($this->view->criterias as  $criterias){
            foreach($criterias as $criteria) {
                    $titles[] =  $criteria;
            }
        }

        $data = array();
        foreach ($this->view->people as $person) {
            $subdata = array();
            $subdata[] = array('value' => $person['user_name'],  'style' => 's21');
            $subdata[] = array('value' => $person['so_name'],  'style' => 's21');
            $subdata[] = array('value' => $person['so_parent'],  'style' => 's21');

            foreach($this->view->criterias as $criterias) {
                foreach($criterias as $cid => $criteria) {
                    if (isset($person['results'][$cid])) {

                        $k =  $person['results'][$cid] /  ($person['competences'][$cid]?$person['competences'][$cid]:1);
                        $style = ($k > 1)?'sgreen':(($k < 1)?'sred':'syellow');

                        $subdata[] = array(
                            'value' => $person['results'][$cid],
                            'style' => $style
                        );
                    }
                    else {
                        $subdata[] = array(
                            'value' => '',
                            'style' => 's21'
                        );
                    }
                }
            }
            $data[] =  $subdata;
        }


$xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

        $xml .= '<Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Arial Cyr" x:CharSet="204"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="sred">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
      <Interior  ss:Color= "#ff5050" ss:Pattern="Solid" />
  </Style>
  <Style ss:ID="sgreen">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
      <Interior  ss:Color= "#33cc33" ss:Pattern="Solid" />
  </Style>
  <Style ss:ID="syellow">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
      <Interior  ss:Color= "#ffcc00" ss:Pattern="Solid" />
  </Style>
 </Styles>';

        $xml .= '<Worksheet ss:Name="' . $title  . '" ss:Description="' .  $title  . '"><ss:Table>';

        $xml .= '<ss:Column ss:Width="200"/>';
        $xml .= '<ss:Column ss:Width="100"/>';
        $xml .= '<ss:Column ss:Width="100"/>';

        $xml .= '<ss:Row>';
        foreach ( $titles as $value ) {

            //$type = ! is_numeric ($value ['value'] ) ? 'String' : 'Number';
            $type = 'String';

            $value = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value);
            $xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value  . '</Data></ss:Cell>';
        }
        $xml .= '</ss:Row>';

        if (is_array ( $data )) {
            foreach ( $data as $row ) {

            $xml .= '<ss:Row>';
                foreach ( $row as $value ) {
                    $value['value'] = strip_tags($value['value']);
                    $type = 'String';
                    $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);


                    $xml .= '<ss:Cell ss:StyleID="'.$value['style'].'"><Data ss:Type="' . $type . '">' . $value['value'] . '</Data></ss:Cell>';
                }
                $xml .= '</ss:Row>';
            }
        }

        $xml .= '</ss:Table></Worksheet>';
        $xml .= '</Workbook>';


        $request = Zend_Controller_Front::getInstance()->getRequest();
        $contentType = strpos($request->getHeader('user_agent'), 'opera') ? 'application/x-download' : 'application/excel';


        /*
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        */

        header('Content-type: '.$contentType);
        header('Content-Disposition: attachment; filename="' . 'result' . '.xls"');
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Pragma: public");
        header("Content-Transfer-Encoding: binary");

        echo $xml;
        exit;
    }
}
