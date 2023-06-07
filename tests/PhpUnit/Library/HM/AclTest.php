<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 6/3/19
 * Time: 5:46 PM
 */
namespace tests\Library\HM;

use PHPUnit\Framework\TestCase;

class AclTest extends TestCase
{
    public function testCanAddUserModuleResources()
    {
        $acl = new \HM_Acl();
        $userAclClassName = 'HM_Acl_User';
        $this->assertEquals(false , class_exists($userAclClassName, false));
        $acl->addModuleResources('user');
        $this->assertEquals(true , class_exists($userAclClassName, false));

    }

    public function testProperlyHandleUserModuleName()
    {
        $acl = new \HM_Acl();
        $moduleName = 'user';
        $this->assertEquals(false , $acl->hasModuleResources($moduleName));
        $acl->storeModuleName($moduleName);
        $this->assertEquals(true , $acl->hasModuleResources($moduleName));

    }

    public function testDetectIsUserAllowed()
    {
        $acl = new \HM_Acl();
        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'index');
        $acl->addResource(new \Zend_Acl_Resource($resource));
        $currentRole = \Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();

        $this->assertEquals(false , $acl->isCurrentAllowed($resource));

        $acl->allow($currentRole, $resource);

        $this->assertEquals(true, $acl->isCurrentAllowed($resource));
    }

    public function testDetectRoleInheritance()
    {
        $acl = new \HM_Acl();
        $roleA = 'a';
        $roleB = 'b';
        $roleC = 'c';
        $acl->addRole(new \Zend_Acl_Role($roleA));
        $acl->addRole(new \Zend_Acl_Role($roleB));
        $acl->addRole(new \Zend_Acl_Role($roleC), $roleA);

        $this->assertEquals(true, $acl->inheritsRole($roleC, $roleA));
        $this->assertEquals(false, $acl->inheritsRole($roleC, $roleB));
    }
}