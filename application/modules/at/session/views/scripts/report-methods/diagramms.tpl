<?php $this->reportChartJS();
    foreach($this->charts['competences'] as $key=>$diagramm) {
        echo "[%".'cluster_'.$key."]";
        echo $this->reportChartJS(
            $diagramm['data'], 
            $diagramm['graphs'], 
            array(
                'id' => 'cluster_'.$key,
                'type' => 'radar',        
                'maxValue' => $this->scaleMaxValue,

                //Соответствует параметрам в \library\phantomjs\html.js
                'width' => 900, 
                'height' => 700,
            ),
            array()
        );
        echo "[".'cluster_'.$key."%]";
    }
?>

