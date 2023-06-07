<?php

class HM_Validate_HasCorrectAnswers extends Zend_Validate_Abstract
{

    const NO_VARIANTS = 'noVariants';
    const TOO_MANY = 'tooMany';

    protected $_messageTemplates = [
        self::NO_VARIANTS => "Вопрос должен содержать хотя бы один правильный ответ",
        self::TOO_MANY => "Вопрос предполагает один правильный ответ"
    ];

    /**
     * Валидатор проверяет наличие хотя бы одного правильного ответа
     *
     * @param HM_Form_Element_Vue_MultiSet $value
     * @return bool
     */
    public function isValid($value)
    {
        $variants = $value->getValue();

        $correct = 0;
        foreach ($variants as $key => $variant) {
            if ($key === 'new') {
                foreach ($variant['is_correct'] as $newCorrectFlag) {
                    if ($newCorrectFlag == 1) $correct++;
                }
            } else {
                if ($variant['is_correct']) $correct++;
            }
        }

        if (!$correct) {
            $this->_error(self::NO_VARIANTS);
            return false;
        }

        if ($correct > 1 || $correct < 1) {
            $this->_error(self::TOO_MANY);
            return false;
        }

        return true;
    }
}
