<?php

/**
 * Description of SearchControllerTest
 *
 * @author tutrinov
 */
class Candidate_SearchControllerTest extends PHPUnit_Extensions_Selenium2TestCase {
    
    public static $inst = null;
    
    public function setUp() {
        if (static::$inst === null) {
            static::$inst = $this;
            $this->setBrowserUrl("http://4g/");
            $this->setBrowser("firefox");
            $this->shareSession(true);
        }
    }
    
    public function testIndexAction() {
        $this->url("/");
        $this->timeouts()->implicitWait(20000);
        $this->byCssSelector(".login.action-link")->click();
        $this->byId("login")->value("admin");
        $this->byId("password")->value("pass");
        $this->byId("submit")->click();
        $this->timeouts()->implicitWait(20000);
        $this->byCssSelector(".hm-roleswitcher-right")->click();
    }
    
    public static function tearDownAfterClass() {
        //parent::tearDownAfterClass();
    }
    
}
