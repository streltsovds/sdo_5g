<?php
class HM_Validate_Deputy extends Zend_Validate_Abstract
{

    protected $_nameDateBegin = null;
    protected $_nameDateEnd = null;
    protected $_messageTemplates = array(
        'used' => "На указанный диапазон дат уже назначен заместитель!",
        'dateErr1' => "Конечная дата не может быть меньше начальной",
        'dateErr2' => "Указанный диапазон дат в прошлом",
        'userUsed' => "Указанный пользователь уже назначен заместителем на указанный момент времени"
    );
    public function __construct($name)
    {
        if (is_array($name)) {
            if (array_key_exists('nameDateBegin', $name)) {
                $this->_nameDateBegin = $name['nameDateBegin'];
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Не задано имя поля 'Дата начала'");
            }

            if (array_key_exists('nameDateEnd', $name)) {
                $this->_nameDateEnd = $name['nameDateEnd'];
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Не задано имя поля 'Дата окончания'");
            }

        } else {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Не заданы имена полей 'Дата начала' и 'Дата окончания'");
        }

    }


    public function isValid($value)
    {
        $dateBegin = $_REQUEST[$this->_nameDateBegin];
        $dateEnd = $_REQUEST[$this->_nameDateEnd];

        $dateBegin = new DateTime($dateBegin);
        $dateEnd = new DateTime($dateEnd);

        $dateBegin = $dateBegin->format('Ymd 00:00:00');
        $dateEnd = $dateEnd->format('Ymd 00:00:00');

        $result = true;
        $this->_setValue($value);
        if ($dateBegin && $dateEnd) {


            $dateBeginDat = new DateTime($dateBegin);
            $dateEndDat = new DateTime($dateEnd);
            $currDate = new DateTime();


            $diff1 = $dateEndDat->diff($dateBeginDat);
            $diff2 = $dateEndDat->diff($currDate);

            if (! $diff1->invert) {
                $this->_error('dateErr1');
                return false;
            }

            if (! $diff2->invert ) {
                $this->_error('dateErr2');
                return false;
            }

            $result0 = Zend_Registry::get('serviceContainer')->getService('Deputy')->validDeputyUser($value, $dateBegin, $dateEnd);
            if ($result0 === false ) $this->_error('used');
            $result &= $result0;

            $result0 = Zend_Registry::get('serviceContainer')->getService('Deputy')->validDeputy($dateBegin, $dateEnd);
            if ($result0 === false ) $this->_error('used');
            $result &= $result0;
        }
        return $result;
    }
}