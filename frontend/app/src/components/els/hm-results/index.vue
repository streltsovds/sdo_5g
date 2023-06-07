<template>
  <div
    id="hm-result"
    class="hm-result"
    :style="{width: size[0]+'px', height: size[1]+'px'}">
    <div v-if="score != -1"
         class="hm-result-noprogress"
    >
      <div v-if="scaleId == 1">
        <span v-if="score">{{ !isNaN(score)  ? score : 0 }}</span>
        <span v-else>0</span>
      </div>
      <div v-else-if="scaleId == 2">
        <div v-if="score == 1">
          <svg-icon name="checkmark" :color="'#05C985'"/>
        </div>
        <div v-if="score != 1">
          <svg-icon name="checkmark" :color="'gray'"/>
        </div>
      </div>
      <div v-else-if="scaleId == 3">
        <div v-if="score == 0">
          <svg-icon name="cross" :color="'#EE423D'"/>
        </div>
        <div v-else-if="score == 1">
          <svg-icon name="checkmark" :color="'#05C985'"/>
        </div>
        <div v-else-if="score == -1">
          <divc class="hm-result-data">
            <span>{{ !isNaN(score) ? progress : 0  }} </span>
          </divc>
          <hm-diagramm
            style="transform: rotate(-90deg)"
            :chart-data=" !isNaN(score) ? progress.replace(/\D+/g,'') : 0"
          />
        </div>
      </div>
    </div>
    <div v-else>
      <div class="hm-result-data">
        <span v-if="progress">{{ !isNaN(progress.replace(/\D+/g,'')) ? progress : 0 }} </span>
        <span v-else><svg-icon name="checkmark" :color="'rgba(30,30,30,0.13)'"/></span>
      </div>
      <hm-diagramm
        :size="{width: size[0], height: size[1]}"
        style="transform: rotate(-90deg)"
        :chart-data=" !isNaN(progress.replace(/\D+/g,'')) && progress ? progress.replace(/\D+/g,'') : 0"
      />
    </div>
  </div>
</template>

<script>


    import SvgIcon from "@/components/icons/svgIcon";
    import HmDiagramm from "@/components/media/hm-diagramm/index";

    export default {
        components: {HmDiagramm, SvgIcon},
        props: {
            size: {
                type: Array,
                default: () => [75, 75]
            },
            score: {type: [String, Number], default: ''},
            scaleId: {type: [String, Number], default: ''},
            progress: {type: [String, Number], default: ''}
        },
        mounted() {

        }
    }
</script>

<style lang="scss">
  #hm-result {
    position: relative;
    .hm-result {
      width: 100%;
      height: 100%;

      &-noprogress {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 3px solid #4A90E2;
        border-radius: 50%;
        > div {
          > span {
            color: #1e1e1e;
            font-size: 16px;
            letter-spacing: 0.02em;

          }
        }
      }
    }

    .hm-result-data {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1;

      > span {
        color: #1e1e1e;
        font-size: 16px;
        letter-spacing: 0.02em;

      }
    }
  }

</style>
