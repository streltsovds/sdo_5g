<?php

class HM_View_Helper_Panels
{
    public function panels($panels = [])
    {
        $formattedPanels = [];

        foreach ($panels as $panel) {
            $formattedPanels[] = [
                'name' => $panel['name'],
                'content' => array_key_exists('content', $panel)
                    ? str_replace("'", "Ⓠ", $panel['content'])
                    : ''
            ];
        }

        $panels = json_encode($formattedPanels);

        return <<<HTML
<hm-panels
    :panels='$panels'
>
</hm-panels>
HTML;

    }
}