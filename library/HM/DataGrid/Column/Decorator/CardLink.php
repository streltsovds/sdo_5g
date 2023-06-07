<?php

/**
 *
 */
class HM_DataGrid_Column_Decorator_CardLink implements HM_DataGrid_Column_Decorator_CardLinkInterface
{
    private $value;

    private function __construct(HM_DataGrid $dataGrid, array $cardLinkArray, array $linkArray)
    {
        $this->value = (($cardLinkArray['url'] == '' && $cardLinkArray['text'] == '') ? '' :
            $dataGrid->getView()->cardLink($cardLinkArray['url'], $cardLinkArray['text'])) .
            '<a href = "'  .   $linkArray['url']  .  '">'  .  $linkArray['text']  .  '</a>';
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Метод для определения конкретных ссылок и их заголовков, из которых будет конструироваться декоратор.
     *
     * @param HM_DataGrid $dataGrid
     * @param array $data
     */
    static public function create(HM_DataGrid $dataGrid, array $data = [])
    {
    }

    /**
     * @param HM_DataGrid $dataGrid
     * @param array       $cardLinkArray - массив типа ['url', 'text']
     * @param array       $linkArray     - массив типа ['url', 'text']
     * @return string
     */
    static public function createInstance(HM_DataGrid $dataGrid, array $cardLinkArray, array $linkArray)
    {
        if (!isset($cardLinkArray['url' ])) $cardLinkArray['url' ] = '';
        if (!isset($cardLinkArray['text'])) $cardLinkArray['text'] = '';
        if (!isset(    $linkArray['url' ]))     $linkArray['url' ] = '';
        if (!isset(    $linkArray['text']))     $linkArray['text'] = '';

        $self = new self($dataGrid, $cardLinkArray, $linkArray);
        return $self->getValue();
    }
}
