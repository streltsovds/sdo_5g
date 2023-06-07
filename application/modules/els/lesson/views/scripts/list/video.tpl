    <?php echo $this->headSwitcher(
            array('module' => 'proctored', 'controller' => 'list', 'action' => 'video', 'switcher' => 'proctored'),
            null,
            $this->disabledSwitcherMods);
    ?>


<?= $this->proctorWindow(); ?>
<?= $this->grid; ?>
<script>

document.addEventListener('DOMContentLoaded', function () {
    var proctorWindow = document.getElementById('proctoringWindow')
    var proctorIframe = proctorWindow.querySelector('iframe')
    var fioEl = document.getElementById('fio');
    var fioLinkEls = document.querySelectorAll('td.grid-fio a')
    var proctorLinkEls = document.querySelectorAll('td.grid-watch a')
    proctorLinkEls.forEach = Array.prototype.forEach

    proctorLinkEls.forEach(function(link, index) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var href = link.href;
            var fioText = fioLinkEls[index].innerText;
            proctorIframe.src = '';
            fioEl.textContent = '';
            setTimeout(function() {  
                proctorIframe.src = href;
                fioEl.textContent = fioText;
                if (!window.isOpened) {
                    window.openProctorWindow();
                }
            }, 100);
        })
    })


})
</script>