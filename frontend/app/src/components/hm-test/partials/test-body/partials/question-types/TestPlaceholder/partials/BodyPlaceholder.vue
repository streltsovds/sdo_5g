<template>
  <div class="hm-test-placeholder__body">
    <template v-for="(component, index) in sumArray">
      <template v-if="component && !component.is">
        <p :key="index" v-html="component"> </p>
      </template>
      <template v-else-if="!component">
        <br :key="index"/>
      </template>
      <component :is="component.is"
                 :key="index"
                 v-bind="component"
                 v-else
                 @change="$emit('setValue', component.id, $event)"
      />
    </template>
  </div>
</template>
<script>

import TextPlaceholder from "./TextPlaceholder";
import SelectPlaceholder from "./SelectPlaceholder";
import BodyPlaceholder from "./BodyPlaceholder";
import ImagePlaceholder from "./ImagePlaceholder";


export default {
  components: {
    TextPlaceholder,
    SelectPlaceholder,
    BodyPlaceholder,
    ImagePlaceholder
  },
  props: {
    text: {
      type: String,
      default: () => {}
    },
    components:{
      type: Array,
      default: () => []
    },
    result:{
      type: Object,
      default: () => {}
    }
  },
  computed:{
    componentsRenderArray(){
      let componentsProps = [];

      this.components.forEach((item) => {
        componentsProps.push({
          is: item.mode.component,
          variants: item.variants,
          options: item.mode.options ? item.mode.options : null,
          value: this.result[item.id],
          id: item.id
        })
      });

      return componentsProps;
    },
    textRenderArray(){
      let splited = this.stripHtml(this.text).replace(/\[\s+/g,'[').split('[]');
      splited = splited.map(item => item.split('[ ]')).flat();
      return splited;
    },
    sumArray(){
      const text = this.textRenderArray;
      const components = this.componentsRenderArray;
      let sumArray = [];

      for(let i = 0; text.length > i; i++){
        if(text[i]){
          if(text[i].includes('\n')) {
            for (let l = 0; l < text[i].split('\n').length; l++) {
              const item = text[i].split('\n')[l]
              if(item.includes('src=')) {
                const arrData = item.replace('src=', '').split('||').flat();
                sumArray.push({
                  is: 'ImagePlaceholder',
                  src: arrData[0],
                  width: arrData[1],
                  height: arrData[2]
                });
              } else {
                sumArray.push(item)
              }
              if(l < text[i].split('\n').length - 1) {
                sumArray.push(false)
              }
            }
          } else {
            sumArray.push(text[i])
          }
        }
        if(components[i]){
          sumArray.push(components[i])
        }
      }

      return sumArray;
    }
  },
  methods:{
    stripHtml(html){
      let tmp = document.createElement("DIV");
      tmp.innerHTML = html;
      const img = tmp.querySelectorAll('img');
      img.forEach(item => {
        const p = document.createElement('p');
        const src = item.src;
        p.innerHTML = `src=${src}||${item.width}||${item.height}`
        item = item.parentNode.replaceChild(p, item);
      });
      return tmp.textContent || tmp.innerText || "";
    },
  }
};
</script>
<style lang="scss">
.hm-test-placeholder__body{
  // display: flex;
  // align-items: center;
  // flex-wrap: wrap;
  p{
    white-space: normal;
    color: #000;
    font-weight: 400;
    font-size: 16px;
    display: inline-flex;
    flex-wrap: wrap;
    margin-bottom: 0;
  }
}
</style>
