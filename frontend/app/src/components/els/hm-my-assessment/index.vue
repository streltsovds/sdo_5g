<template>
  <div id="hmMyAssessment">
    <div class="hm-my-assessment">
      <hm-my-assessment-card
        v-for="(card, index) in dataAssessment"
        :key="index"
        :dataCard="card"
      />
    </div>
  </div>
</template>
<script>
import HmMyAssessmentCard from "./card"
export default {
  props: ["dataAssessment"],
  components: {HmMyAssessmentCard}
}
</script>
<style lang="scss">
  .hm-my-assessment {
    width: auto;
    height: auto;
    background: #fff;
    box-shadow: 0 10px 30px rgb(209 213 223 / 50%);
    border-radius: 4px;
  }
</style>
