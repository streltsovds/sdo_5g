<template>
  <div class="sidebar-chat" id="sidebarChat">
      <div class="sidebar-chat__img"
        v-if="!dataSidebar.subject.image">
        <img :src="dataSidebar.subject.icon">
        <div class="sidebar-chat__img-info">
          <span>{{ dataSidebar.subject.name | truncate(100)}}</span>
        </div>
        <div class="sidebar-chat__img-edit">
          <hm-sidebar-button-edit v-if="showEditButton" :link="dataSidebar.editUrl"/>
        </div>
      </div>
      <div class="sidebar-chat__image"
        v-else
        :style="{ backgroundImage: `url(${dataSidebar.subject.image})` }">
        <div class="sidebar-chat__image-info">
          <span>{{ dataSidebar.subject.name | truncate(100)}}</span>
        </div>
      </div>
      <hm-recruiters-chat-main />
      <hm-recruiters-chat-fatal-error :error-text="errorText" v-if="errors.fatal !== null" />
      <confirm ref="confirm" />
  </div>
</template>

<script>
import Vue, { PropType as VuePropType } from 'vue';
import Vuetify from 'vuetify';
import { registerStoreModuleDefault, storeModuleMapperDefault, unregisterStoreModuleDefault } from './vuexStoreModule';
import HmRecruitersChatState from './vuexStoreModule/state';
import HmRecruitersChatMain from './components/Main/index.vue';
import HmRecruitersChatFatalError from './components/FatalError/index.vue';
import * as socket from './lib/socket/index';
import registerPushListener from './lib/socket/push';
import registerListeners from './lib/socket/listen';
import messageListener from './lib/socket/listen';
import { error } from 'vuex-smart-module/lib/utils';
import { FatalErrors } from './types/errors';
import Confirm from './components/Confirm/index.vue';
import FileIcon from "./../../../icons/file-icon/index.vue";

export default {
  name: 'HmRecruitersChat',
  components: {
    HmRecruitersChatMain,
    HmRecruitersChatFatalError,
    Confirm,
    FileIcon
  },
  props: {
    initialStoreState: {
      type: Object,
      default: null,
    },
    dataSidebar:{
      type: Object,
      default: null
    }
  },
  computed: {
    ...storeModuleMapperDefault.mapState([
      'wsConfig',
      'errors'
    ]),
    errorText(){
      if(!this.errors.fatal) return '';
      const errorCode = this.errors.fatal;
      return FatalErrors[errorCode]
    }
  },
  created() {
    /**
     * @see VueMixinStoreGridGenerator
     **/
    if (this.$store.state.HmWorkflowGrid) {
      unregisterStoreModuleDefault(this.$store);
    }
    registerStoreModuleDefault(this.$store);
  },
  beforeMount() {
    if (this.initialStoreState) {
      this.stateInit(this.initialStoreState);
    }
  },
  beforeDestroy() {
    if (!module.hot) {
      unregisterStoreModuleDefault(this.$store);
    }
  },
  mounted() {
    this.$root.$confirm = this.$refs.confirm.open;
    this.componentEmitFunctionSet({emitFn: this.$emit.bind(this)});
    const { port, host, ssl, sessionId, namespace } = this.wsConfig;
    const ws = ssl ? "wss" : "ws";
    socket.createConnection(`${ws}://${host}:${port}/message`, sessionId, namespace, this.$store).then(res => {
      socket.registerMessageListener(messageListener, this.$store);
      this.$store.subscribe(registerPushListener);
    }).catch(error => {
      this.throwFatalError({errorCode: 0});
    });
  },
  methods: {
    ...storeModuleMapperDefault.mapMutations([
      'stateInit',
      'componentEmitFunctionSet',
      'throwFatalError'
    ]),
  },
};
</script>

<style lang="sass" src="./styles.sass"/>
