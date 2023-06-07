<?php

class HM_Acl_Storage extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        // Всем запрещен прямой доступ
        $resource = sprintf('mca:%s:%s:%s', 'storage', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);
    }
}