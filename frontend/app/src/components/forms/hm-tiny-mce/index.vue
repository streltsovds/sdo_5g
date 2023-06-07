<template>
  <div class="hm-tiny-mce hm-form-element">
    <label
      v-if="label"
      class="v-label theme--light"
      :class="{ required }"
      v-text="label"
    ></label>
    <p v-if="description" v-html="description"></p>
    <hm-errors :errors="errors"></hm-errors>
<!--    <tiny-mce-->
<!--      v-model="content"-->
<!--      :init="initDataNew"-->
<!--    ></tiny-mce>-->
    <tiny-mce
      :key="keyEditor"
      :disabled="disabled"
      ref="editor"
      v-model="content"
      :name="name"
      :init="initData"
    ></tiny-mce>
    <v-progress-circular
      v-if="!loaded"
      indeterminate
      color="primary"
      class="hm-tiny-mce_loader"
    ></v-progress-circular>
    <input
      ref="uploader"
      class="hm-tiny-mce_uploader"
      type="file"
      accept="image/*"
      @change="uploadByClickBtnInToolbar"
    />
    <hm-elfinder
      v-if="needElfinder"
      :name="elFinderId"
      :attribs="elfinder"
    ></hm-elfinder>
  </div>
</template>
<script>
import HmErrors from "./../hm-errors";
import HmElfinder from "./../hm-elfinder";
import TinyMce from "@tinymce/tinymce-vue";

import ruWords from "./langs/ru";
import hmTinyMceFonts from "./utilities/fonts";
import hmTinyMceFormats from "./utilities/formats";
import hmTinyMceOptions from "./utilities/options";
import hmTinyMceTemplates from "./utilities/templates";
import hmTinyMceButtonsConfig from "./utilities/buttons";
import hmTinyMcePastePostProcess from "./utilities/pastePostProcess/index.js";

/**
 * NOTE: Для пошаговой отладки самого скрипта TinyMCE нужно
 *   задать `wysiwyg.params.script_debug_mode = true` в `application/settings/config.ini`
 *
 * @see php:HM_View_Helper_VueTinyMce
 */

export default {
  name: "HmTinyMce",
  components: { TinyMce, HmErrors, HmElfinder },
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: String,
      default: null
    },
    attribs: {
      type: Object,
      default: () => {}
    },
    errors: {
      type: [Object, Array],
      default: () => {}
    },
    stylePath: {
      type: String,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      label: this.attribs.label || null,
      height: this.attribs.height || null,
      description: this.attribs.description || null,
      required: this.attribs.required || false,
      content: this.value,
      templates: hmTinyMceTemplates,
      configName: this.attribs.toolbar || "hmToolbarTiny",
      elfinder: this.attribs.elfinder || {},
      menubar: false,
      plugins: [],
      toolbars: [],
      buttons: [],
      formats: [],
      fontFormats: "",
      contentCss: [
        this.stylePath,
        "/frontend/libs/tinymce/css/tinymce.css",
        "https://fonts.googleapis.com/css?family=Roboto"
      ],
      isMounting: true,
      initData: {},
      initDataNew: {
        height: this.height || 400,
        language: 'ru',
      },
      language: 'ru',
      loaded: false,
      editor: null,
      elFinderId: "server_file",
      keyEditor: 0
    };
  },
  computed: {
    toolbarsData() {
      let tools = '';

      this.toolbars.forEach(toolbarElements => {
        tools += toolbarElements.join(" ");
      });

      return tools;
    },
    needElfinder() {
      return document.getElementsByClassName("hm-elfinder").length === 0;
    }
  },
  created() {
    if (!hmTinyMceOptions.hasOwnProperty(this.configName)) return;
    let optionForConfig = hmTinyMceOptions[this.configName];

    this.setPlugins(optionForConfig);
    this.setToolbars(optionForConfig);
    this.setButtons(optionForConfig);
    this.setMenubar(optionForConfig);
    this.formats = hmTinyMceFormats;
    this.fontFormats = hmTinyMceFonts;

    this.init()

    // если через 15 сек редактор не инициализировался - перерисовываем его
    const reinitialization = setInterval(() => {
      if(!this.loaded) this.keyEditor++
      else clearInterval(reinitialization)
    }, 15000)
  },

  methods: {
    // setLang(editor) {
    //   if (this.language) return;

    //   editor.editorManager.addI18n("ru", ruWords);
    //   this.language = "ru";

    //   this.reloadEditor();
    // },
    init() {
      this.initData = {
        /** Чтобы изображения заново не сохранялись на сервер при каждом новом открытии редактора */
        automatic_uploads: false,
        height: this.height || 400,
        remove_script_host: false,        language: 'ru',
        // skin: false,
        element_format: "html",
        paste_data_images: true,
        convert_fonts_to_spans: false,
        paste_postprocess: hmTinyMcePastePostProcess,
        menubar: this.menubar,
        plugins: this.plugins.join(" "),
        templates: this.templates,
        content_css: this.contentCss,
        formats: this.formats,
        font_formats: this.fontFormats,
        toolbar: this.toolbarsData,
        relative_urls: false,
        file_picker_callback: (callback, value, meta) => {
          if (this.elFinderId)
            this.$root.$emit(`hmElfinderOpen${this.elFinderId}`, {
              callback,
              meta
            });
        },
        images_upload_handler: (blobInfo, success, failure) => {

          this.allowEdit(false);
          let data = new FormData();

          if (!this.$store.state.user.id) {
            this.allowEdit(true);
            return failure("Ошибка! Неизвестен идентификатор пользователя");
          }

          data.append('reqId', ( +new Date()).toString(16) + Math.floor(1000 * Math.random()).toString(16));
          data.append('cmd', 'upload');
          data.append('target', this.attribs.target_hash);
          data.append('mtime[]', new Date().getTime());
          data.append('ts',  Math.round((new Date()).getTime() / 1000));
          data.append('upload_path[]', this.attribs.target_hash);
          // console.log(this.attribs.target_hash);
          data.append('dropWith', 0);
          data.append(
            "upload[]",
            blobInfo.blob(),
            `${new Date().getTime()}_${blobInfo.filename()}`
          );
          this.$axios
            .post("/storage/index/elfinder", data)
            .then(r => {
              if (
                // r.data.status !== 200 ||
                r.status !== 200 ||
                !r.data ||
                !r.data.rel ||
                !r.data.rel[0]
              )
                throw true;

              success(r.data.rel);
              $(this.editor.dom.select("img.img-temp")[0]).removeClass(
                "img-temp"
              );
            })
            .catch(() => {
              this.editor.selection.select(
                this.editor.dom.select("img.img-temp")[0]
              );
              this.editor.selection.getNode().remove();
              this.allowEdit(true);
              failure("Ошибка! Изображение не загрузилось.");
            })
            .then(() => {
              this.allowEdit(true);
            });
        },
        setup: editor => {
          this.editor = editor;
          editor.on("init", () => {
            this.loaded = true
          });
          editor.on("Change", (e) => {
            this.$emit('getValue', editor.getContent())
          });

          /** После задания контента, пришедшего из базы, возвращаем автосохранение на сервере новых изображений */
          editor.on("SetContent", (_content) => {
            editor.settings.automatic_uploads = true;
          })

          this.addButtons(editor);
        }
      };
    },
    uploadByClickBtnInToolbar() {
      let file = this.$refs.uploader.files[0];
      let fileReader = new FileReader();
      fileReader.onload = () => {
        let img = new Image();
        img.src = fileReader.result;
        this.editor.insertContent('<img src="' + img.src + '"/>');
      };
      fileReader.readAsDataURL(file);
    },
    addButtons(editor) {
      this.buttons.forEach(button => {
        if (hmTinyMceButtonsConfig.hasOwnProperty(button)) {
          let btn = hmTinyMceButtonsConfig[button];
          btn.setEditor(editor);
          editor.addButton(button, {
            text: btn.text,
            icon: btn.icon,
            tooltip: btn.tooltip,
            onclick: btn.onclick.bind(btn),
            onPostRender: function() {
              let setup = () => {
                editor.formatter.formatChanged(button, state => {
                  this.active(state);
                });
              };
              editor.formatter ? setup() : editor.on("init", setup);
            }
          });
        }
      });

      editor.addButton("imageupload", {
        icon: "imageupload",
        onclick: () => {
          $(this.$refs.uploader).trigger("click");
        },
        tooltip: this._("Изображение из файловой системы"),
      });
    },
    allowEdit(editable = true) {
      this.editor.getBody().setAttribute("contenteditable", editable);
    },
    reloadEditor() {
      this.isMounting = true;

      this.$nextTick(() => {
        this.isMounting = false;
        this.loaded = true;
      });
    },

    setConfigByName(optionForConfig, name) {
      if (optionForConfig.hasOwnProperty(name)) {
        this[name] = optionForConfig[name];
      }
    },
    setPlugins(optionForConfig) {
      this.setConfigByName(optionForConfig, "plugins");
    },
    setToolbars(optionForConfig) {
      this.setConfigByName(optionForConfig, "toolbars");
    },
    setButtons(optionForConfig) {
      this.setConfigByName(optionForConfig, "buttons");
    },
    setMenubar(optionForConfig) {
      this.setConfigByName(optionForConfig, "menubar");
    }
  }
};
</script>
<style lang="scss">
@import "~tinymce/skins/lightgray/skin.min.css";
@import "~tinymce/skins/lightgray/content.min.css";

.mce-filepicker .mce-open {
  display: none;
}
.hm-tiny-mce_loader {
  display: block;
  margin: auto;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.hm-tiny-mce {
  margin-top: 35px;

  .hm-elfinder.row {
    margin: 0;
  }
}
.mce-notification-warning {
  display: none;
}
.mce-notification,
.mce-window {
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  top: 50% !important;
  position: fixed;
}
.mce-window,
.mce-reset {
  &.hide {
    display: none;
  }
}
.hm-tiny-mce_uploader {
  display: none;
}
.mce-i-imageupload:before {
  content: "\E034";
}
.mce-i-image:before {
  content: "\E034";
}
input.mce-abs-layout-item {
  width: 40px;
}
@media (max-width: 549px) {
  .mce-window {
    max-width: 100%;
    width: 100% !important;
  }
  .mce-window-body {
    max-width: 100%;
    width: 100% !important;
    .mce-container-body {
      max-width: 100%;
      /*width: 100% !important;*/
    }
    .mce-panel {
      max-width: 100%;
    }
  }
  .mce-form {
    padding-left: 10px;
    padding-right: 10px;
    width: calc(100% - 20px) !important;
    position: initial !important;
    height: auto !important;
  }
  .mce-foot {
    width: 100% !important;
    .mce-container-body {
      padding: 10px;
    }
    .mce-btn {
      position: initial !important;
    }
  }
  .mce-container.mce-formitem {
    height: auto !important;
    margin-bottom: 10px;
    position: initial;
  }
  .mce-container-body .mce-abs-layout {
    height: auto !important;
    overflow: unset;
  }
  .mce-title {
    white-space: normal;
  }
  .mce-label {
    position: unset !important;
    display: block;
    width: 100% !important;
    white-space: normal;
  }
  .mce-abs-layout-item.mce-combobox {
    display: block !important;
    width: 100% !important;
    position: initial !important;
  }
  .mce-abs-layout-item .mce-textbox {
    position: initial !important;
  }

  .mce-formitem .mce-container {
    height: auto !important;
    margin-bottom: 10px;
    position: initial;
    .mce-container-body {
      .mce-label {
        position: absolute !important;
        display: inline-block !important;
        width: auto !important;
      }
      .mce-textbox {
        position: absolute !important;
      }
    }
  }

  .mce-dropzone {
    max-width: 100% !important;
    left: 0 !important;
  }

  .mce-browsebutton {
    left: 0 !important;
    + .mce-label {
      opacity: 0;
    }
  }

  textarea.mce-textbox {
    max-width: 100% !important;
  }

  .mce-iframe {
    position: initial !important;
    width: 100% !important;
    max-width: 100%;
  }
}
</style>
