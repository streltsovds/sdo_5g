<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/29/18
 * Time: 2:45 PM
 */

class HM_Form_SearchSidebar extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('sidebar-search')
            ->setAttrib('class', 'js-sidebar-search')
            ->setAttrib('action', '/resource/search/index/');

        $this->addElement($this->getDefaultTextElementName(), 'search_query', array(
            'class' => 'wide',
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Искать')));


        $classifiersGroups = $this->getService('Classifier')->getKnowledgeBaseClassifiers();
        $classifiersResults = [];

        if (is_array($classifiersGroups)) {
            foreach ($classifiersGroups as $classifierGroupName => $classifiers) {

                $classifiersResults[$classifierGroupName]['title'] = $classifiers['title'];
                $resultItemsBag = [];

                foreach($classifiers['items'] as $classifierKey => $classifier) {
                    $classifierUrl = Zend_Registry::get('view')->url([
                        'module' => 'resource',
                        'controller' => 'catalog',
                        'action' => 'index',
                        'classifier_id' => $classifier->classifier_id,
                    ], null, true);

                    $resultItemsBag[$classifier->classifier_id] = $classifier->name . " ($classifierUrl)";
                }

                $classifiersResults[$classifierGroupName]['items'] = $resultItemsBag;
            }
        }


        $this->addElement(
            $this->getDefaultMultiCheckboxElementName(),
            'classifiers',
            [
                'separator' => '<br>',
                'Required' => false,
                'Label' => '',
                'MultiOptions' => $classifiersResults,
            ]
        );
        $this->addDisplayGroup(
            [
                'search_query',
                'submit',
            ],
            'sidebarGroup1',
            array('legend' => _('Поиск по названию'))
        );

        $this->addDisplayGroup(
            [
                'classifiers'
            ],
            'sidebarGroup2',
            array('legend' => _('Поиск по классификатору'))
        );


        parent::init();

        $this->setDisplayGroupDecorators(['FormElements', 'SearchbarFieldset']);
    }
}