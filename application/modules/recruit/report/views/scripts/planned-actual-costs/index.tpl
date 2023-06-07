<style>
    .grid-filters-from{
        margin-bottom: 10px;
    }
    .grid-filters-from dd,
    .grid-filters-from dt{
        display: inline;
    }
    .grid-filters-from dd{
        margin-right: 5px;
    }

    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 200px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 30%;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Начало периода не может иметь более позднюю дату, чем конец периода.<br>Скорректируйте даты в полях "Период от" и "Период до".</p>
    </div>

</div>

<script>
    $(document).ready(function() {
        $("#submit").click(function(e){
            checkDateOrder(e);
        });
    });

    var modal = document.getElementById('myModal');
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
        modal.style.display = "none";
    };

    function checkDateOrder(e) {
        var periodFromSelector = document.getElementById('period_from');
        var periodToSelector   = document.getElementById('period_to');
        var period_from = periodFromSelector.options[periodFromSelector.selectedIndex].value;
        var period_to   = periodToSelector.options[periodToSelector.selectedIndex].value;
        if (period_from == '0') period_from = '1_1900';
        if (period_to   == '0') period_from = '1_2100';
        if (isDateOrderCorrect(period_from, period_to)) {
            return true;
        } else {
            modal.style.display = "block";
            e.preventDefault ? e.preventDefault() : e.returnValue = false;
        }
    }

    function isDateOrderCorrect(from, to) {
        var fromArray = from.split('_').map(function(i) {
            return parseInt(i, 10)
        });

        var toArray   = to.split('_').map(function(i) {
            return parseInt(i, 10)
        });

        if (fromArray[1] >= toArray[1] && fromArray[0] > toArray[0]) {
            return false;
        }

        return true;
    }
</script>

<?php if (!$this->isAjaxRequest):?>
    <form class="grid-filters-from" method="post">
        <?php echo $this->selectFrom; ?>
        <?php echo $this->selectTo; ?>
        <?php echo $this->submit; ?>
    </form>
<?php endif;?>
<?php echo $this->grid;?>