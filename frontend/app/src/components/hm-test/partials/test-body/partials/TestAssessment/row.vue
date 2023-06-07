<template>
  <div
    class="hm-assessment-form__row"
    :class="{'hm-assessment-form__row_active': selectedOption}"
  >
    <div class="hm-assessment-form__row-cell hm-assessment-form__row-cell_first" :style="{width: `calc(100% - ${widthNameIndicator}px)`}">
      <p>{{ name }}</p>
    </div>
    <v-radio-group
      class="hm-assessment-form__row-buttons"
      @change="onAnswerChosen"
      :value="selectedOption"
    >
      <v-tooltip
        v-for="scaleValue in scaleValues"
        :key="`${scaleValue.value_id}-${id}`"
        bottom
      >
        <template #activator="{ on, attrs }">
          <v-radio
            class="hm-assessment-form__row-cell"
            v-bind="attrs"
            v-on="on"
            :value="scaleValue.value_id"
          />
        </template>
        <span>{{ scaleValue.description }}</span>
      </v-tooltip>
    </v-radio-group>
  </div>
</template>
<script>
export default {
  props: {
    selectedAnswer: {
      type: Number,
      default: null
    },
    name: {
      type: String,
      default: ""
    },
    indicatorInfo: {
      type: Object,
      default: () => {}
    },
    id: {
      type: Number,
      default: null
    },
    scaleValues: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      selected: null
    }
  },
  computed: {
    selectedOption() {
      if (this.selectedAnswer && !this.selected) {
        return this.selectedAnswer;
      } else if (this.selected) {
        return this.selected;
      } else {
        return null;
      }
    },
    widthNameIndicator() {
      if(this.$vuetify.breakpoint.smAndDown) return this.scaleValues.length * 100
      else if(this.$vuetify.breakpoint.md) return this.scaleValues.length * 140
      else return this.scaleValues.length * 184

    }
  },
  methods: {
    onAnswerChosen(payload) {
      this.isBtnDisabled = false;
      this.selected = payload;
      this.onAnswerConfirm();
    },
    onAnswerConfirm() {
      this.$log.debug(this.selected);
      this.$nextTick(() => {
        this.$emit("hm:test:answer-confirmed", {
          question: this.id,
          answers: this.selected,
          indicatorInfo: this.indicatorInfo
        });
      });
    }
  }
}
</script>
