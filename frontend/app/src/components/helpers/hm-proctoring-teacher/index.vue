<template>
  <hm-proctoring v-if="url" :key="url" :student-event-url="url" type="teacher" :name="name" />
</template>
<script>
import HmProctoring from '../hm-proctoring';
import { mapMutations } from 'vuex';
export default {
  name: "hmProctoringTeacher",
  components: {
    HmProctoring
  },
  computed: {
    url() {
      return this.$store.getters['proctoring/GET_URL'];
    },
    name() {
      return this.$store.getters['proctoring/GET_NAME'];
    },
  },
  methods: {
    ...mapMutations(['proctoring/SET_URL', 'proctoring/SET_NAME']),

  },
  mounted() {
    this['proctoring/SET_URL']("")
    this['proctoring/SET_NAME']("")
  }
}
</script>
