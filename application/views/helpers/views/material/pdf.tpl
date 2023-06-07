<?php if ($this->materialContentUrl):?>
    <vue-pdfjs
        class="hm-material-pdf"
        url="<?php echo $this->materialContentUrl; ?>"
    ></vue-pdfjs>
<!--    <hm-pdf-viewer src="--><?php //echo $this->materialContentUrl; ?><!--" :options="{'page': 1}"></hm-pdf-viewer>-->
<!--    <iframe src="--><?php //echo $this->materialContentUrl; ?><!--"></iframe>-->
<?php endif;?>
