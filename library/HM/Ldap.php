<?php
class HM_Ldap extends Zend_Ldap
{
    public function getEntry($dn, array $attributes = array(), $throwOnNotFound = false)
    {
        $entry = parent::getEntry($dn, $attributes, $throwOnNotFound);
        if ($entry) {
            foreach($entry as $key => $values) {
                if (is_array($values) && count($values)) {
                    foreach($values as $index => $value) {
                        $entry[$key][$index] = iconv('UTF-8', Zend_Registry::get('config')->charset, $value);
                    }
                } else {
                    $entry[$key] = iconv('UTF-8', Zend_Registry::get('config')->charset, $values);
                }
            }
        }
        return $entry;
    }

    public function findUserByLogin($login)
    {
        $dn = $this->getCanonicalAccountName(
            iconv(Zend_Registry::get('config')->charset, 'UTF-8', $login),
            Zend_Ldap::ACCTNAME_FORM_DN
        );
        if ($dn) {
            return $this->getEntry($dn);
        }
        return null;
    }

    public function setLdapOptions($ldapName)
    {
        if (!empty($ldapName)) $this->_options = Zend_Registry::get('config')->$ldapName->ldap->options->toArray();
    }

    private function _prepareOptions($options)
    {
        $return = array();
        
        $permittedOptions = array(
            'host'                   => null,
            'port'                   => 0,
            'useSsl'                 => false,
            'username'               => null,
            'password'               => null,
            'bindRequiresDn'         => false,
            'baseDn'                 => null,
            'accountCanonicalForm'   => null,
            'accountDomainName'      => null,
            'accountDomainNameShort' => null,
            'accountFilterFormat'    => null,
            'allowEmptyPassword'     => false,
            'useStartTls'            => false,
            'optReferrals'           => false,
            'tryUsernameSplit'       => true,
        );        
        
        if (!is_array($options)) return false;
        foreach ($options as $key => $value) {
            if (strpos($key, 'ad') !== false) {
                $key = lcfirst(str_replace('ad', '', $key));
                if (array_key_exists($key, $permittedOptions)) {
                    $return[$key] = $value;
                }
            }
        }
        return $return;
    }
}