<template>
  <div>
    <span v-show="!status" class="hm-forum__tab-title">{{ title }}</span>
    <input ref="input" v-show="status" :value="title" @change="sendNewText" />
  </div>
</template>
<script>
export default {
  props: ['text', 'url', 'sectionId', 'changeSectionId'],
  data() {
    return {
      title: this.text
    }
  },
  computed: {
    status() {
      if(this.sectionId === this.changeSectionId) {
        this.$nextTick(() => {
          this.$refs.input.focus();
        });
        return true
      }
      else return false
    }
  },
  methods: {
    sendNewText(e) {
      const formData = new FormData();
      formData.append('title', e.target.value);
      fetch(this.url, {
        method: 'POST',
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
        body: formData
      }).then(res => res.json())
        .then(data => {
          if(data.status === 'success') {
            this.title = e.target.value;
            this.$emit('changeStatus');
          }
        })
        .catch(err => {
          console.log(err)
        })
    }
  }
 }
</script>
