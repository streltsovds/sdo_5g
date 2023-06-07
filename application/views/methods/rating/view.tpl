<?php
    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
    $containerId = $this->id('at-form');
?>
<script>

    (function () {

        function PeopleTree() {
            this.above = {}; // люди, которые лучше
            this.less = {};  // люди, которые хуже
        }

        PeopleTree.prototype = {

            check: function() {
                this.checkAllAbove();
                this.checkAllLess();
            },

            throwError: function(index) {

                var result = [];

                for (var i in index) {
                    if (!index.hasOwnProperty(i)) {
                        continue;
                    }

                    result.push(i);
                }

                throw result.join(',');
            },

            clone: function(obj) {

                var result = {};

                for (var i in obj) {
                    if (!obj.hasOwnProperty(i)) {
                        return;
                    }
                    result[i] = obj[i];
                }

                return result;
            },

            checkAllLess: function(result) {
                if (!result) {
                    result = {};
                }

                for (var lessPeopleId in this.less) {
                    if (!this.less.hasOwnProperty(lessPeopleId)) {
                        continue;
                    }
                    if (!result.hasOwnProperty(lessPeopleId)) {
                        var nextResult = this.clone(result);
                        nextResult[lessPeopleId] = lessPeopleId;
                        this.less[lessPeopleId].checkAllLess(nextResult);
                    } else {
                        this.throwError(result);
                    }
                }

                return result;
            },

            checkAllAbove: function(result) {
                if (!result) {
                    result = {};
                }

                for (var abovePeopleId in this.less) {
                    if (!this.above.hasOwnProperty(abovePeopleId)) {
                        continue;
                    }
                    if (!result.hasOwnProperty(abovePeopleId)) {
                        var nextResult = this.clone(result);
                        nextResult[abovePeopleId] = abovePeopleId;
                        this.above[abovePeopleId].checkAllAbove(nextResult);
                    } else {
                        this.throwError(result);
                    }
                }

                return result;
            }

        };

        var containerId = '#<?php echo $containerId ?>',
            pairCompareItems = [];

        function QuestRating(rowCont) {

            pairCompareItems.push(this);

            this.$el   = $(containerId);
            this.$rows = $(rowCont);

            var peopleIndex = {},
                peoplePare = {},
                me = this;

            $inputs = this.$rows.find('input[type="radio"]');
            $inputs.on('change', _.bind(this.onChangeRow, this));

            $inputs.on('click', function() {

                var me = this;

                setTimeout(function() {
                    $(me).trigger('change');
                }, 0);

            });

            $inputs.each(function() {
                peoplePare[this.name] = peoplePare[this.name] || [];
                peoplePare[this.name].push(this.value);

                if (!peopleIndex.hasOwnProperty(this.value)) {
                    peopleIndex[this.value] = new PeopleTree();
                }
            });


            this.peoplePare = peoplePare;
            this.peopleIndex = peopleIndex;

            $inputs.each(function() {
                if (this.checked) {
                    me.setUserSelect(this.name, this.value);
                }
            });

        }

        QuestRating.prototype = {

            hasConflict: function() {

                try {
                    for (var i in this.peopleIndex) {
                        if (!this.peopleIndex.hasOwnProperty(i)) {
                            continue;
                        }
                        this.peopleIndex[i].check();
                    }
                } catch (e) {
                    return true;
                }

                return false;
            },

            setUserSelect: function(pareName, curPeopleId) {
                var pare = this.peoplePare[pareName],
                    parePeopleId = (pare[0] == curPeopleId) ? pare[1] : pare[0];

                var curPeople = this.peopleIndex[curPeopleId],
                    parePeople = this.peopleIndex[parePeopleId];

                delete curPeople.above[parePeopleId];
                curPeople.less[parePeopleId] = parePeople;

                delete parePeople.less[curPeopleId];
                parePeople.above[curPeopleId] = curPeople;

                this.$rows.find('td').removeClass('hm-quest-raiting-item-conflict');

                try {
                    curPeople.check();
                    parePeople.check();
                } catch (e) {
                    var itemsConflict = e.split(',');

                    for (var i = 0; i < itemsConflict.length; i++) {
                        this.$rows.find('td[data-hm_people_id="'+itemsConflict[i]+'"]').addClass('hm-quest-raiting-item-conflict');
                    }

                }

            },

            onChangeRow: function(e) {

                var input = e.target,
                        curPeopleId = input.value,
                        pare = this.peoplePare[input.name],
                        parePeopleId = (pare[0] == curPeopleId) ? pare[1] : pare[0];

                if (input.checked) {
                    this.setUserSelect(input.name, curPeopleId);
                } else {
                    this.setUserSelect(input.name, parePeopleId);
                }

            }
        };

        QuestRating.hasConflict = function() {

            for (var i = 0; i < pairCompareItems.length; i++) {
                if (pairCompareItems[i].hasConflict()) {
                    return true;
                }
            }

            return false;

        };

        hm.QuestRating = QuestRating;

    })();
</script>
<div class="at-competence at-form hm-at-competence-form-quest-rating">

    <div class="tests_header">
    <?php // @todo: рефакторить этот кусок unmanaged ?>
    <table  border="0" cellspacing="0" cellpadding="0" class="tests_main" style="width:100%;">
    	<tr>
    		<td class="header_first_td" align="left" valign="middle"><?= $this->model['event']->name ?></td>
            <td class="header_three_td" align="right">
                <div style="display:none;"><?=_("Время не ограничено")?></div>
                <div class="progress">
                    <div class="progress_load" id="progress_percent"></div>
                </div>                
                <div class="progress_stop">
                    <a href="<?= $this->resultsUrl ?>" class="progress_stop"><img style=" width: 16px; height: 17px; border: none;" alt="" src="/images/content-modules/tests/break.gif"></a>
                </div>
                <div style='position:relative; top:2px; float:left;color:#fff'><?php echo _('Время не ограничено');?></div>
    		</td>
    	</tr>
    </table>
    </div>
    <div class="at-form-wrapper">
    <?= $this->questProgress($this->progress, array('target' => $containerId))?>

    <?php if ($comment = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('ratingСomment')): ?>
    <div class="at-form-header">
        <div class="at-form-comment">
            <?= $comment;?>
        </div>
    </div>
    <?php endif;?>
    <div class="at-form-body">
        <div id="<?= $containerId ?>" class="at-form-container">
            <?= $this->action('load', 'rating', 'event') ?>
        </div>
    </div>
    </div>
</div>
<script>

</script>
