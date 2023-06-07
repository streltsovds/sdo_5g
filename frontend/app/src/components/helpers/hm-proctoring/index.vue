<template>
  <div
    class="hm-proctoring__window"
    :class="{'hm-proctoring__window--isExpanded': isExpand ? true : false}"
    :style="{height: isOpened ? '90vh' : '35px'}"
  >
    <div v-if="isOpened" class="hm-proctoring__buttons">
      <div @click.stop="reload" class="hm-proctoring__reload"></div>
      <div v-if="typeProctoring === 'teacher'" @click.stop="expand" class="hm-proctoring__expand"></div>
    </div>
    <div id="hider" class="hm-proctoring__hider">
      <div class="hm-proctoring__toolbar">
        <div v-if="name" class="hm-proctoring__fio-field">{{ name }}</div>
        <div @click="toggleProctorWindow" class="hm-proctoring__hide-open-el hm-proctoring__back"></div>
        <span @click="toggleProctorWindow" class="hm-proctoring__hide-open-el hm-proctoring__icon-wrapper">
          <svg :style="{transform: !isOpened ? 'rotate(180deg)' : 'none'}" class="hm-proctoring__icon hm-proctoring__icon--opened" version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 255" width="20" height="20">
            <path d="M0 64l128 127L255 64z"/>
          </svg>
        </span>
        <span class="hm-proctoring__field">
        </span>
      </div>
    </div>
    <div class="hm-proctoring__iframe">
      <iframe allow="microphone; camera; display-capture" :key="key" :src="iframeUrl"></iframe>
    </div>
  </div>
</template>
<script>
export default {
  name: "hmProctoring",
  props: ['studentEventUrl', 'type', 'name'],
  data() {
    return {
      isOpened: true,
      isExpand: false,
      iframeUrl: this.studentEventUrl,
      key: 1,
      typeProctoring: this.type && this.type !== 'student' ? 'teacher' : 'student'
    }
  },
  methods: {
    // removeChatEffect(e){
		// 	hider.removeAttribute('style');
		// 	e.target.removeEventListener('click', removeChatEffect);
    // },
    toggleProctorWindow() {
      this.isOpened = !this.isOpened;
      this.isExpand = false;
		},
    reload() {
      this.key++
    },
    expand() {
      this.isExpand = !this.isExpand;
    }
  }
}
</script>
<style lang="scss">
.hm-proctoring {
  &__window {
    position: fixed;
    display: block;
    bottom: 0;
    right: 0;
    width: 320px;
    height: 90vh;
    background-color: #fff;
    border-color: #fff;
    color: rgba(0,0,0,0.87);
    border-top-left-radius: 13px;
    transition: 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
    box-shadow: 0px 11px 15px -7px rgba(0,0,0,0.2), 0px 24px 38px 3px rgba(0,0,0,0.14), 0px 9px 46px 8px rgba(0,0,0,0.12);
    text-decoration: none;
    overflow: hidden;
    z-index: 1000;
  }
  &__fio-field {
    height: 25px;
    background: transparent;
    position: absolute;
    width: 228px;
    left: 35px;
    right: 25px;
    color: white;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 25px;
    font-size: 1rem;
    top:6px;
    white-space: nowrap;
  }
  &__buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    position: absolute;
    right: 5px;
    top: 6px;
    z-index: 100;
    height: 25px;
  }
  &__expand {
    height: 25px;
    width: 25px;
    background-image: url(/images/fullscreen.svg);
    background-size: 17px;
    background-repeat: no-repeat;
    background-position: center;
    cursor: pointer;
    margin-left: 5px;

  }
  &__reload {
    height: 25px;
    width: 25px;
    background-image: url(/images/reload.svg);
    background-size: 17px;
    background-repeat: no-repeat;
    background-position: center;
    cursor: pointer;

  }
  &__window--isExpanded {
    top: 1rem;
    bottom: 1rem;
    left: 1rem;
    right: 1rem;
    width: calc(100vw - 3rem) !important;
    height: calc(100vh - 2rem) !important;
    box-shadow: 0px 11px 15px -7px rgba(0,0,0,0.2), 0px 24px 38px 3px rgba(0,0,0,0.14), 0px 9px 46px 8px rgba(0,0,0,0.12) !important;
  }
  &__iframe {
    width: 100%;
    height: calc(100% - 38px);
    & iframe {
      width: 100%;
      height: 100%;
    }
  }
  &__hider{
    height: 38px;
    background-color: #62bf6e;
    cursor: pointer;
    border-bottom: 1px solid grey;
    border-radius: 13px 0 0 0;
  }
  &__toolbar{
    display: flex;
    height: 100%;
    position:relative
  }
  &__back{
    position:absolute;
    width:100%;
    height:100%;
    top:0;
    left:0;
    z-index:0;
  }
  &__icon-wrapper{
    margin-right: 14px;
    position: relative;
    z-index: 0;
    width: 14px;
  }
  &__field{
    color: white;
    font-size: 16px;
    text-transform: uppercase;
    line-height: 25px;
    font-weight: bold;
    vertical-align: bottom;
    margin-left: -2px;
  }
  &__icon {
    margin-left: 10px;
    margin-top: 10px;
    margin-right: 10px;
    fill: #f5f5f5;
    width: 14px;

    &--closed {
      transform: rotate(180deg);
    }
  }
}
</style>
