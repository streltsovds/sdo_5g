<template>
  <div class="hm-multi-select-areas-image">
    <v-file-input
      @change="getFile"
      show-size
      accept="image/png, image/jpeg, image/bmp"
      prepend-icon="mdi-camera"
      label="Загрузить картинку"
      messages="Загрузите изображение и разметьте активные области"
    />
    <div class="hm-multi-select-areas-image__selector-wrapper" v-if="imgSrc" v-show="imgIsLoad">
      <multi-select-areas-image
        @img-onload="onImgIsLoad"
        :crop-url="imgSrc"
        :pos-correction="false"
        :width="WIDTH"
        :areas-model="areas"
        @getListAreas="getListAreas"
      />
    </div>
    <v-progress-circular v-if="!imgIsLoad && imgSrc"
                         indeterminate
                         color="primary"
                         class="mt-5 ml-5"
    />
    <ul class="hm-multi-select-areas-image__answers">
      <hm-multi-select-areas-image-answer v-for="(area) in areas"
                                          :area="area"
                                          :key="area.id"
                                          @removeArea="removeArea"
                                          @set-area="setArea"
      />
    </ul>
    <div class="hm-multi-select-areas-image__show">
      <input :value="(+showVariants_)"
             name="show_variants"
             type="hidden"
      >
      <v-checkbox
        v-model="showVariants_"
        label="Показывать варианты ответов при прохождении теста"
      />
    </div>
  </div>
</template>
<script>
import HmMultiSelectAreasImageAnswer from "./partials/answer.vue";
import MultiSelectAreasImage from "@/libs/multi-select-areas-image/MultiSelectAreasImage";
import axios from "axios";

export default {
  name: "HmMultiSelectAreasImage",
  components: {
    MultiSelectAreasImage,
    HmMultiSelectAreasImageAnswer
  },
  props:{
    imgInitial:{
      type: String,
      default: null,
    },
    areasInitial:{
      type: Array,
      default: () => []
    },
    showVariants: {
      type: String,
      default: '0',
    }
  },
  data(){
    return {
      areas: [],
      imgSrc: this.imgInitial,
      WIDTH: 800,
      showVariants_: false,
      imgIsLoad: false
    }
  },
  mounted(){
    if(this.areasInitial){
      this.areas = this.areasInitial;
    } 
    this.showVariants_ = !!(+this.showVariants);
  },
  methods: {
    onImgIsLoad(){
      this.imgIsLoad = true;
    },
    removeArea(id){
      this.areas = this.areas.filter(item => item.id !== id);
    },
    setArea(area){
      let areas1 = [...this.areas];
      let aIndex = areas1.findIndex(item => item.id === area.id);
      areas1[aIndex] = area;

      this.areas = areas1
    },
    getListAreas(value) {
      this.areas = value
    },
    // Загружаем картинку на сервер
    uploadImage(file){
      const formData = new FormData();
      formData.append('image_map_input', file);
      axios.post('/file/upload/save', formData).then(res => {
        this.setFileId(res.data.id);
      });
    },
    setFileId(fileId){
      let fileIdInput = document.querySelector('[name="file_id"');
      fileIdInput.value = fileId;
    },
    dataURLtoBlob(dataurl) {
      var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
          bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
      while(n--){
        u8arr[n] = bstr.charCodeAt(n);
      }
      return new Blob([u8arr], {type:mime});
    },
    getFile(file) {
      this.uploadImage(file);

      var resize_width = this.WIDTH;

      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => {

        var img = new Image();
        img.src = reader.result
        new Promise((resolve)=> {
          img.onload = function(el) {
            var elem = document.createElement('canvas');

            var scaleFactor = resize_width / el.target.width;
            elem.width = resize_width;
            elem.height = el.target.height * scaleFactor;

            var ctx = elem.getContext('2d');
            ctx.drawImage(el.target, 0, 0, elem.width, elem.height);

            var srcEncoded = ctx.canvas.toDataURL(el.target, 'image/jpeg', 0);  

            resolve(srcEncoded);          
          }
        }).then(res => {
          this.uploadImage(this.dataURLtoBlob(res))
          this.imgSrc = res;
          this.areas = [];
        })
      }
    },
  }
};
</script>
<style lang="scss">
.hm-multi-select-areas-image {
  &__selector-wrapper{
    position: relative;
  }
  &__answers{
    padding-left: 0!important;
  }
  *,
  :after,
  :before {
    box-sizing: content-box;
  }
}
</style>
