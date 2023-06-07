<template>
  <v-layout row justify-center class="hm-elfinder">
    <v-container v-if="!isModal" fill-height>
      <v-layout justify-center align-center wrap>
        <v-flex xs12>
          <div class="hm-elfinder_body"><div ref="elfinder"></div></div>
        </v-flex>
      </v-layout>
    </v-container>
    <v-dialog
      v-else
      :value="dialog"
      fullscreen
      hide-overlay
      transition="dialog-bottom-transition"
    >
      <v-card>
        <v-toolbar dark color="primary">
          <v-btn icon dark :small="$vuetify.breakpoint.xsOnly" @click="close">
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title
            :class="{ 'subheading ml-0': $vuetify.breakpoint.xsOnly }"
            >Файловое хранилище</v-toolbar-title
          >
        </v-toolbar>
        <v-card class="hm-elfinder_card">
          <v-container fill-height>
            <v-layout justify-center align-center wrap>
              <v-flex xs12>
                <div class="hm-elfinder_body"><div ref="elfinder"></div></div>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card>
      </v-card>
    </v-dialog>
  </v-layout>
</template>
<script>
export default {
  name: "HmElfinder",
  props: {
    name: {
      type: String,
      default: null
    },
    attribs: {
      type: Object,
      default: () => {}
    },
    transport: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      value: null,
      dialog: this.isOpen,
      elfinder: null,
      meta: {},
      callback: null,
      isOpen: false,
      connectorUrl: this.attribs.connectorUrl || "/storage/index/elfinder",
      lang: this.attribs.lang || "ru",
      startPathHash: 'l1_' + btoa('/').replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.').replace(/\.+$/, ''),
      isModal:
        typeof this.attribs.isModal === "boolean" ? this.attribs.isModal : true,
      ui: this.attribs.ui || ["toolbar", "tree", "path", "stat"]
    };
  },
  watch: {
    isOpen(v) {
      this.dialog = v;
      let action = this.dialog ? "add" : "remove";

      document.getElementById("mce-modal-block").classList[action]("hide");
      document
        .getElementsByClassName("mce-window")[0]
        .classList[action]("hide");
    }
  },
  mounted() {
    window.addEventListener("load", () => {
      this.init();
    });

    this.$root.$on(`hmElfinderOpen${this.name}`, ({ callback, meta }) => {
      this.callback = callback;
      this.meta = meta;
      this.isOpen = true;
    });
  },
  methods: {
    init() {
      this.elfinder = jQuery(this.$refs.elfinder)
        .elfinder({
          // ui : ['path', 'stat'], для скрытия тулбара и дерева директорий
          ui: this.ui,
          url: this.connectorUrl,
          places: "",
          lang: this.lang,
          startPathHash: this.startPathHash,
          transport: this.transport == "v1" ? new elFinderSupportVer1() : false,
          toolbar: [
            ["reload"],
            ["select", "open"],
            ["mkdir", "upload"],
            ["rename", "comment", "copy", "paste", "rm"],
            ["info"]
          ],
          contextmenu: {
            cwd: [
              "reload",
              "delim",
              "mkdir",
              "upload",
              "paste",
              "delim",
              "info"
            ],
            file: [
              "select",
              "open",
              "copy",
              "cut",
              "rm",
              "rename",
              "comment",
              "info"
            ],
            group: ["copy", "cut", "rm", "info"]
          },
          dialog: { width: 500, modal: true, title: "Файловое хранилище" },
          closeOnEditorCallback: true,
          // editorCallback: (path) => {
          //   let imgExts = ["jpg", "jpeg", "gif", "bmp", "png"];
          //   let extension = path.substr(path.lastIndexOf(".") + 1);
          //   path = path.replace(/^.*\/\/[^\/]+/, "");
          //   this.preview = path;
          //   // this.isImg = imgExts.includes(extension);
          // },
          getFileCallback: (file, fm) => {
            //https://github.com/Studio-42/elFinder/wiki/Integration-with-TinyMCE-4.x
            let url, info;

            if (!file) return;

            // URL normalization
            url = fm.convAbsUrl(file.url);
            // Make file info
            info = `${file.name} (${fm.formatSize(file.size)})`;

            // Provide file and text for the link dialog
            if (this.meta.filetype == "file") {
              this.callback(url, { text: info, title: info });
            }

            // Provide image and alt text for the image dialog
            if (this.meta.filetype == "image") {
              this.callback(url, { alt: info });
            }

            // Provide alternative source and posted for the media dialog
            if (this.meta.filetype == "media") {
              this.callback(url);
            }
            this.close();
          }
        })
        .elfinder("instance");
    },
    close() {
      this.isOpen = false;
    }
  }
};
</script>
<style lang="scss">
.hm-elfinder_card {
  height: calc(100% - 56px);
  width: 100%;
  position: fixed;
}
</style>
