<?php $idPrefix = $this->id('reg'); ?>
<div id="<?= $idPrefix; ?>-form">
<?php echo $this->form?>
<?php echo $this->ContractOfferFields?>
</div>
<?php $this->inlineScript()->captureStart()?>
(function () {
    var formId = <?= HM_Json::encodeErrorSkip("{$idPrefix}-form"); ?>;
    $(document.body).delegate('#' + formId + ' *[id="refresh"]', 'click', function (event) {
            event.preventDefault();
            // @todo: refresh    
    });
})();
<?php $this->inlineScript()->captureEnd()?>

