<?php

// @todo-int
class HM_Integration_Soap_RequestExport
{
    static public function get(Array $item)
    {
        $fields = array();
        foreach ($item as $key => $value) {
            $field = <<<FIELD
<ns1:Field>
	<ns1:Name>{$key}</ns1:Name>
    <ns1:Value>{$value}</ns1:Value>
    <ns1:CodeNSI></ns1:CodeNSI>
    <ns1:Type></ns1:Type>
</ns1:Field>
FIELD;
            $fields[] = $field;
        }

        $fields = implode("\r\n", $fields);

        $message = <<<MSG
<ns1:Message>
    <ns1:Source>СДО</ns1:Source>
    <ns1:DataTable>
        <ns1:Id>a15e3c4a9273571211e1e1bca34de55d</ns1:Id>
        <ns1:Entity>РСВ_ ИТ_ЗагруженныеЗначенияПоказателейИзПериметра</ns1:Entity>
        <ns1:Field>
        </ns1:Field>
        <ns1:Table>
            <ns1:Name>ИТ_ЗагруженныеЗначенияПоказателейИзПериметра</ns1:Name>
            {$fields}
        </ns1:Table>
    </ns1:DataTable>
</ns1:Message>
MSG;
        return $message;
    }
}