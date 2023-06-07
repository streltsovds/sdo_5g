# Удалённый код (заметки)

## Обход ограничения vue 2 на один элемент в корне

`src/components/form-components/hm-file/partials/uploadedItem.vue`:

```
<script type="text/jsx">
import HmFileUploadedFile from "./uploadedFile";
import { compact } from "lodash";

export default {
  name: "HmFileUploadedItem",
  functional: true,
  components: {HmFileUploadedFile},
  props: {
    uploadedItem: {
      type: Object,
      default: () => {
      },
    },
  },
  render(h, {props, listeners, slots} = []) {
    let originalFile = props.uploadedItem.originalFile;
    let file = props.uploadedItem.file;

    let onBtnDeleteClick = () => {
      this.$emit("on-btn-delete", props.uploadedItem);
    };

    return compact([
      <hm-file-uploaded-file
        file={file}
        show-btn-delete={!originalFile}
      />,
      (
        originalFile
          ? <hm-file-uploaded-file
              file={originalFile}
              show-btn-delete={true}
            />
          : null
      )
    ]);
  }
}
</script>

```
