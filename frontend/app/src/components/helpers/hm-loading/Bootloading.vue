<template>
  <div class="bootloader" :style="{background: fill}">
    <div>
      <svg id="Layer_1"
           width="358"
           height="358"
           viewBox="0 0 358 358"
           fill="none"
           xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M179 353.28C275.252 353.28 353.28 275.252 353.28 179C353.28 82.7477 275.252 4.71983 179 4.71983C82.7477 4.71983 4.71983 82.7477 4.71983 179C4.71983 275.252 82.7477 353.28 179 353.28Z"
          fill="white"
          stroke="rgba(0, 165, 81, 0.15)"
          stroke-width="8.56034"
          style="animation:BOOTLOADER_OPACITY_Stroke 2000ms ease-in 0ms infinite"
        />
        <path
          d="M120.379 259.912C117.741 259.912 115.103 258.74 113.345 256.981C111.586 255.222 110.414 252.584 110.414 249.946V172.274C110.414 166.705 114.81 162.308 120.379 162.308C125.948 162.308 130.345 166.705 130.345 172.274V239.981H230.586V172.274C230.586 169.636 231.759 166.998 233.517 165.239C235.276 163.481 237.914 162.308 240.552 162.308H256.966L180.466 98.4119L88.7241 174.912C84.3276 178.429 78.1724 177.843 74.6552 173.74C71.1379 169.343 71.7241 163.188 75.8276 159.671L174.017 77.8947C177.828 74.6705 183.103 74.6705 186.914 77.8947L285.103 159.671C287.448 161.722 288.621 164.36 288.621 167.291V172.567C288.621 178.136 284.224 182.533 278.655 182.533H250.81V250.239C250.81 252.877 249.638 255.515 247.879 257.274C246.121 259.033 243.483 260.205 240.845 260.205H120.379V259.912Z"
          fill="rgba(0, 165, 81, 0.15)"
          style="animation:BOOTLOADER_OPACITY_Fill 2000ms ease-in 0ms infinite"
        />
      </svg>
    </div>
  </div>
</template>
<script>
    export default {
        props: {
            fill: {
                type: String,
                default: ''
            }
        }
    }
</script>
<style lang="scss">
  .bootloader {
    width: 100%;
    height: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 500000;
    display: flex;

    > div {
      width: 358px;
      height: 358px;
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }
  }

  @keyframes BOOTLOADER_OPACITY_Stroke {
    0% {
      stroke: rgba(0, 165, 81, 0.15)
    }
    50% {
      stroke: rgba(0, 165, 81, 0.25)
    }
    100% {
      stroke: rgba(0, 165, 81, 0.15)
    }
  }

  @keyframes BOOTLOADER_OPACITY_Fill {
    0% {
      fill: rgba(0, 165, 81, 0.15)
    }
    50% {
      fill: rgba(0, 165, 81, 0.55)
    }
    100% {
      fill: rgba(0, 165, 81, 0.15)
    }
  }
</style>


