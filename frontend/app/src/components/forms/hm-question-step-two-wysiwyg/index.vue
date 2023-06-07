<template>
  <div class="hm-question-step-two-wysiwyg">
    <div class="hm-question-step-two-wysiwyg__block" v-for="index in elementsNumber" :key="index">
      <hm-checkbox
        :name="`${defaultElementName}_checkbox_${index}`"
        label=""
      />
      <hm-tiny-mce
        :name="`${defaultElementName}_variant_${index}`"
      />
    </div>
  </div>
</template>
<script>

import HmCheckbox from '@/components/forms/hm-checkbox';

export default {
  name: "HmQuestionStepTwoWysiwyg",
  components: {
    HmCheckbox
  },
  props: {
    elementsNumber: {
      type: Number,
      default: 3
    }
  },
  data() {
    return {
      defaultElementName: 'variants',
      currentElementsNumber: this.elementsNumber,
    };
  },
  computed:{
  }
};
</script>
