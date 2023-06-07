<template>
  <v-file-input
    label="Загрузите файл"
    hint="Максимальный размер загружаемого файла: 10MB /
          Разрешённые типы файлов: jpg, png, gif, jpeg, mp3, wav, aac, ogg /
          Допустимое количество файлов: 1"
    persistent-hint
    accept="image/png, image/jpeg, image/jpg, image/gif, audio/mp3, audio/wav, audio/aac, audio/ogg"

    :loading="loadingStatus"
  />
</template>
<script>
export default {
  props: ['data'],
  data() {
    return {
      loadingStatus: false,
      deleteUrl: ""
    }
  },
  methods: {
    // uploadFile() {
    //   this.loadingStatus = true
    //   fetch(url, {
    //     headers: {
    //       'X_REQUESTED_WITH': 'XMLHttpRequest',
    //     },
    //   })
    //     .then(data => {
    //       if(data.delete_url) this.deleteUrl = data.delete_url
    //       if(data.id) this.sendAnswer(data.id)
    //     })
    //     .catch(error => console.error(error))
    // },
    sendAnswer(result) {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", result);
      });
    }
  }
}
</script>
