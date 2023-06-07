<?php if (count($this->videos)) $video = $this->videos[0]; ?>
<hm-video-block
        :videos='<?php echo HM_Json::encodeErrorSkip($this->videos) ?>'
        edit-url='<?php echo $this->baseUrl($this->url(array("module" => "video", "controller" => "list", "action" => "index")))?>'
        video-url='<?php echo $this->baseUrl($this->url(array("module" => "video", "controller" => "list", "action" => "get-embedded", "videoblock_id" => "")))?>'
        show-edit='<?php echo $this->showEditLink ?>'
        current-role="<?= $this->currentRole ?>"
>
</hm-video-block>
