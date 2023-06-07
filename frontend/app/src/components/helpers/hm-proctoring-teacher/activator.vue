<template>
  <a @click="openProctoringWindow" :style="{color: status ? 'color: rgb(33 149 48);' : ''}" title="Открыть окно прокторинга">{{name}}</a>
</template>
<script>
import { mapMutations } from 'vuex';
export default {
  props: ['url', 'name'],
  data() {
    return {
      status: false
    }
  },
  computed: {
    activeUrl() {
      return this.$store.getters['proctoring/GET_URL'];
    },
  },
  watch: {
    activeUrl(){
      if(this.activeUrl !== this.url) this.status = false;
    }
  },
  methods: {
    ...mapMutations(['proctoring/SET_URL', 'proctoring/SET_NAME']),
    openProctoringWindow() {
      let proctoringUrl = this.url;
      let proctoringName = this.name;
      this.status = !this.status;
      if(!this.status) {
        proctoringUrl = "";
        proctoringName = "";
      }
      this['proctoring/SET_URL'](proctoringUrl);
      this['proctoring/SET_NAME'](proctoringName);
    }
  }
}
</script>
