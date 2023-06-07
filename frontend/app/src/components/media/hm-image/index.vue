<template>
  <div
    class="hm-image"
    style="display: flex; justify-content: center;"
  >
<!--    align-items: start-->
    <!--@TODO разобраться почему v-img некорректно загружает картинку (также и после ctrl+f5)-->
    <!--<v-img contain :max-height="imgHeight" :max-width="imgWidth" :src="imgSrc" />-->
    <img class="containedImg" :src="imgSrc" />
  </div>
</template>

<script>
export default {
  props: {
    imgSrc: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      imgWidth: 0,
      imgHeight: 0
    };
  },
  mounted() {
    let img = new Image();
    img.src = this.$props.imgSrc;
    this.imgWidth = img.width;
    this.imgHeight = img.height;
  }
};
</script>

<style scoped lang="scss">
.containedImg {
  max-height: 100%;
  max-width: 100%;
  object-fit: cover;
}
</style>
