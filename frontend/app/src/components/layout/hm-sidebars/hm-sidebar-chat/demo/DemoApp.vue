<template>
  <div class="hm-recruiters-chat-demo">
    <v-app>
      <v-main>
        <hm-recruiters-chat />
      </v-main>
    </v-app>
  </div>
</template>

<script lang="ts">
import Vue from "vue";
import HmRecruitersChat from '~/index.vue';
import demoAppData from './demoAppData'
import DemoMouseState from '$common/components/helpers/mouse-state/demo/index.vue';

export default Vue.extend({
  name: 'DemoApp',
  components: {
    HmRecruitersChat
  },
  data() {
    return {
      console,
      date: null,
      dateTimePopupOpen: false,
      /** Начальные данные компонента */
      initialStoreState: demoAppData,
    }
  },
  methods: {
    getWindow(): any {
      return window;
    }
  }
});
</script>
<style lang="sass" src="./demoAppStyles.sass" />
<!--<style src="vue-datetime/dist/vue-datetime.css" />-->
