<template>
  <div class="hm-recruiters-chat__fatal-error">
    <!-- <p>{{ errorText }}</p> -->
    <p class="hm-recruiters-chat__fatal-error-text">Разорвано соединение с сервером. Попробуйте обновить страницу.</p>
  </div>
</template>

<script>
import Vue, { PropType as VuePropType } from 'vue';

import colorsForJs from '../../colorsForJs.sass';
import { storeModuleMapperDefault } from '../../vuexStoreModule';
import Recruiter from '../../types/recruiter';

export default Vue.extend({
  name: 'HmRecruitersChatFatalError',
  props:{
    errorText: {
      type: String,
      default: ''
    }
  },
});
</script>
<style lang="sass" src="./index.sass"/>
