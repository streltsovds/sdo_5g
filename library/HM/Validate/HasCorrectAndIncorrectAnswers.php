<?php

class HM_Validate_HasCorrectAndIncorrectAnswers extends Zend_Validate_Abstract
{

    const NO_VARIANTS = 'noVariants';
    const TOO_MANY = 'tooMany';

    protected $_messageTemplates = [
        self::NO_VARIANTS => "Необходимо указать правильный вариант ответа",
        self::TOO_MANY => "Вопрос предполагает один правильный ответ"
    ];

    /**
     * Валидатор проверяет наличие хотя бы одного правильного и неправильного ответов
     *
     * @param HM_Form_Element_Vue_MultiSet $value
     * @return bool
     */
    public function isValid($value)
    {
        $variants = $value->getValue();

        $correct = 0;
        $incorrect = 0;
        foreach ($variants as $key => $variant) {
            if ($key === 'new') {
                foreach ($variant['is_correct'] as $newCorrectFlag) {
                    $newCorrectFlag == 1 ? $correct++ : $incorrect++;
                }
            } else {
                $variant['is_correct'] ? $correct++ : $incorrect++;
            }
        }

        if (!$correct || !$incorrect) {
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
