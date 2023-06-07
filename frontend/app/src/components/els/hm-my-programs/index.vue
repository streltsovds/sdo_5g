<template>
  <div class="hm-my-programs">
    <span style="display: none">{{ isShowCourses = true }}</span>
    <div class="hm-my-programs__program" v-for="(program, index) in programsData" :key="index">
      <hm-my-programm :program="program" :isShowCourses="isShowCourses" />
      <span style="display: none">{{ isShowCourses = false }}</span>
    </div>
  </div>
</template>

<script>

import HmMyProgramm from './partials/program.vue';

export default {
  components: {
    HmMyProgramm
  },
  props: {
    programsData:{
      type: Array,
      default: () => {}
    }
  }
}
</script>

<style lang="scss">
.hm-my-programs{
    min-height: 100%;
    &__program {
      margin-top: 26px;
      &:first-child {
        margin-top: 0;
      }
    }
}
</style>
