<?php

/**
 * Description of StrategyFactoryTest
 *
 * @author tutrinov
 */
class StrategyFactoryTest extends PHPUnit_Framework_TestCase {
    
    protected static $bootstrap = null;
    protected static $application = null;
    
    public function setUp() {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }
    
    public function appBootstrap() {
        static::$application = new Zend_Application(APPLICATION_ENV,
            APPLICATION_PATH .DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'config.ini');
        static::$application->bootstrap();
    }
    
    public function strategySources() {
        $this->appBootstrap();
        return array(
            array('internal'),
            array('external'),
            array('reinternal'),
            array('undefined')
        );
    }
    
    /**
     * @covers HM_Recruit_Candidate_Search_StrategyFactory::getStrategy($source)
     * @dataProvider strategySources
     */
    public function testGetStrategy($source) {
        try {
            try {
                $searchStrategyFactory = Zend_Registry::get('serviceContainer')->getService('RecruitCandidateSearchStrategyFactory');
            } catch (\Exception $e) {
                $this->fail('Candidate search strategy factory doesn\'t defined');
            }
            $this->assertTrue(method_exists($searchStrategyFactory, 'getStrategy'));
            $strategy = $searchStrategyFactory->getStrategy($source);
            $this->assertTrue($strategy instanceof HM_Recruit_Candidate_Search_SearchBehavior);
        } catch (InvalidArgumentException $e) {
            $this->setExpectedException('HM_Recruit_Candidate_Search_Exception_InvalidSearchStrategyException');
            throw $e;
        }
    }
    
}
