<template>
  <a v-if="el.url || el.viewUrl " :href="el.url || el.viewUrl">
    <div class="kbase-card-icon" :style="{backgroundColor: alphaCol(el.filetype) || alphaCol(el.type) || alphaCol(el.kbase_type)}">
      <div class="kbase-card-icon-noPhoto" ref="svgIcon">
        <file-icon :type="iconType()" :small="breakPointsIcon" />
      </div>
    </div>
  </a>
  <hm-kbase-icon v-else />
</template>


<script>
import 'swiper/dist/css/swiper.css'
import typeIcons from '@/components/icons/file-icon/types'
import FileIcon from "@/components/icons/file-icon/index";
import hexDec from '@/utilities/hexDec';

export default {
  name: "HmKbaseIcon",
  components: {FileIcon },
  props: {
    el: {
      type: Object,
      default: () => {
      }
    },
  },
  computed: {
    breakPointsIcon() {
      return this.$vuetify.breakpoint.width <= 516;
    }
  },
  methods: {
    alphaCol(el) {
      if(typeIcons[el]) {
        return hexDec(typeIcons[el].color);
      }
    },
    iconType(){
      let type;

      if(this.el.type === 'external'){
        if(this.el.filetype === 'unknown'){
          type = this.el.type;
        }else{
          type = this.el.filetype;
        }
        
      }else{
        if(this.el.type){
          if(this.el.type === 'unknown' || this.el.type === '0'){
            type = this.el.kbase_type
          }else{ 
            type = this.el.type;
          }
        }else{
          type = this.el.kbase_type;
        }
        
      }

      return type;
    },
    styleIcon(el) {
      return el.imageUrl && el.imageUrl !== ''  ? {backgroundImage: `url(${el.imageUrl})`} : '';
    },
    typeIcon(el) {
      return el !== '' ? el : 'default';
    },
  }
};
</script>