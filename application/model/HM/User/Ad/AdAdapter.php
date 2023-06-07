<?php
class HM_User_Ad_AdAdapter extends HM_Adapter_Import_Abstract
{
    /*
     * HM_Ldap
     */
    private $_ldap = null;

    public function __construct(Zend_Ldap $ldap = null)
    {
        if (null === $ldap) {
            $ldap = Zend_Registry::get('serviceContainer')->getService('Ldap');
        }

        $this->_ldap = $ldap;
    }

    public function getLdap()
    {
        return $this->_ldap;
    }

    public function needToUploadFile()
    {
        return false;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $ldapNames = null)
    {
        $return = array();
        $ldapNames = $ldapNames ?: HM_Integration_Abstract_Model::getLdapNames();

        foreach ($ldapNames as $ldapName) {
            $return[$ldapName] = $this->fetchAllByLdap($ldapName);
        }

        return $return;
    }

    public function fetchAllByLdap($ldapName)
    {
        $values  =
        $return  = array();
        if (!method_exists(Zend_Registry::get('config')->$ldapName->ldap, 'toArray')) return false;
        $options = Zend_Registry::get('config')->$ldapName->ldap->toArray();

        if (!isset($options['adQuery'])) $options['adQuery'] = '';

        foreach($options['mapping']['user'] as $key => $value) {
            $values[] = $key;
        }

        $uniqueId = $options['mapping']['user']['uniqueIdField'];
        $adQuery  = $options['adQuery'];
        $limit    = $options['fetchItemsLimit'];
        $alphabet = 'qwertyuiopasdfghjklzxcvbnm';
        $count    = 0;
        $this->_ldap->setOptions($options['options']);

        for ($i = 0; $i < strlen($alphabet); $i++) {
            if ($count >= 5) {
                $this->_ldap->disconnect();
                $this->_ldap->bind();
            }
            $alpha = substr($alphabet, $i, 1);
            $dn = null;
            $result = $this->search($alpha, $uniqueId, $adQuery, $dn, $values);

            if (count($result) >= $limit) {
                for($j = 0; $j < strlen($alphabet); $j++) {
                    if ($count >= 5) {
                        $this->_ldap->disconnect();
                        $this->_ldap->bind();
                    }

                    $beta = substr($alphabet, $j, 1);
                    $result = $this->search($alpha . $beta, $uniqueId, $adQuery, $dn, $values);

                    if (count($result)) {
                        $return = array_merge($return, $result->toArray());
                    }

                    $count++;
                }
                continue;
            }

            if (count($result)) {
                $return = array_merge($return, $result->toArray());
            }
            $count++;
        }

        return $return;
    }

    private function search($samaccountname, $uniqueId, $adQuery, $dn, $values)
    {
        return $this->_ldap->search(
            '(&(samaccountname=' . $samaccountname .'*)(objectclass=user)'.
            '(!(userAccountControl:1.2.840.113556.1.4.803:=2))'.
            '(' .$uniqueId . '=*)' . $adQuery . ')',
            $dn,
            Zend_Ldap::SEARCH_SCOPE_SUB,
            $values
        );
    }

}