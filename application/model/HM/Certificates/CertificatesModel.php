<?php
class HM_Certificates_CertificatesModel extends HM_Model_Abstract
{
	// #28685
    const TYPE_CERTIFICATE_ELS = -1; //  Вид документа - самопальный сертификат
    const TYPE_CERTIFICATE = 0; //  Вид документа сертификат
    const TYPE_DIPLOMA     = 1; //  Вид документа диплом
	const TYPE_TICKET      = 2; //  Вид документа удостоверение

    static public function getCertificateTypes()
    {
        return array(
            self::TYPE_CERTIFICATE_ELS => _('Электронное свидетельство'),
            self::TYPE_CERTIFICATE => _('Сертификат'),
            self::TYPE_DIPLOMA     => _('Диплом'),
            self::TYPE_TICKET      => _('Удостоверение')
        );
    }
}