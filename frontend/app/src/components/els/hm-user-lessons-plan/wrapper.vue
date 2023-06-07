<template>
  <div class="hm-user-lessons-plan">
    <div class="hm-user-lessons-plan__switch-wrapper">
      <hm-switch-checkmark
      v-model="switchStatus"
      :label="_('Показать все:')"
      @change="getFormatData"
      label-left-side />
    </div>
    <hm-user-lessons-plan-row
      v-for="(item, index) in formatData"
      :key="index"
      :array-card="item"
    >
    </hm-user-lessons-plan-row>
    <hm-user-lessons-plan-section v-for="section in formatDataSections" :key="section.section_id" :name="section.name" :lessons="section.lessonAssigns" :expanded="section.expanded" />
  </div>
</template>

<script>
import HmUserLessonsPlanRow from "./row";
import HmSwitchCheckmark from "../../controls/hm-switch-checkmark";
import HmUserLessonsPlanSection from "./section";
export default {
  props: ["data", "sections"],
  components: {
    HmUserLessonsPlanRow,
    HmSwitchCheckmark,
    HmUserLessonsPlanSection
  },
  data() {
    const defaultSwitchStatus = true;

    return {
      switchStatus: defaultSwitchStatus,
      formatData: null,
      formatDataSections: null
    }
  },
  mounted() {
    this.getFormatData(this.switchStatus);
  },
  methods: {
    getFormatData(value) {
      if(!value) {
        const newData = [];
        const newSectionsData = [];
        if(this.data) {
          const copyData = JSON.parse(JSON.stringify(this.data));
          copyData.forEach((item) => {
            if(!item.isPassed) newData.push(item);
          });
        }
        if(this.sections) {
          const copySections = JSON.parse(JSON.stringify(this.sections));
          copySections.forEach((item) => {
            const arr = [];
            item.lessonAssigns.forEach(lesson => {
              if(!lesson.isPassed) arr.push(lesson);
            });
            item.lessonAssigns = arr;
            newSectionsData.push(item);
          });
        }
        this.formatData = newData;
        this.formatDataSections = newSectionsData;
      } else {
        this.formatData = this.data;
        this.formatDataSections = this.sections;
      }
    }
  }
}
</script>
<style lang="scss">
  .hm-user-lessons-plan {
    position: relative;
    &__switch-wrapper {
      display: flex;
      align-items: center;
      position: absolute;
      right: 20px;
      top: -51px;

      & .v-input--selection-controls {
        margin: 0 !important;
        padding: 0 !important;
      }
    }

    &__switch {
      margin: 0 !important;
      padding: 0 !important;

    }
  }
  @media(max-width: 1200px) {
    .hm-user-lessons-plan {
      &__switch-wrapper {
        top: -92px;
      }
    }
  }
</style>
