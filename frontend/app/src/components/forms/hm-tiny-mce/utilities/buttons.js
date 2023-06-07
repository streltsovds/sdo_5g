class ButtonTinyMCE {
  constructor({
    text = null,
    tooltip = null,
    icon = false,
    onclick,
    tag = null,
    className = null,
    toggleClass = null
  }) {
    this.tag = tag;
    this.className = className;
    this.toggleClass = toggleClass;
    this.text = text;
    this.tooltip = tooltip;
    this.icon = icon;
    this.onclick = this[onclick];
    this.editor = null;
  }
  setEditor(editor) {
    this.editor = editor;
  }
  mceToggleFormat() {
    if (!this.editor) {
      return console.error("HMTinyMCE: Editor for button undefined");
    }

    if (!this.getSelectionContent()) {
      let node = this.editor.selection.getNode();
      this.editor.selection.select(node);
    }

    const { getSelectedBlocks } = this.editor.selection;
    const selectedBlocks = getSelectedBlocks();

    this.editor.execCommand("RemoveFormat");
    this.editor.execCommand("mceToggleFormat", false, this.tag);

    if (selectedBlocks.length > 0 && selectedBlocks[0].localName === this.tag) {
      return this.editor.insertContent(`<p>${this.getSelectionContent()}</p>`);
    }
  }

  mceBlockQuote() {
    this.editor.execCommand("mceBlockQuote");
  }

  tagWithClass() {
    if (!this.editor) {
      return console.error("HMTinyMCE: Editor for button undefined");
    }

    let content = this.getSelectionContent();

    if (!content) {
      this.editor.selection.select(this.editor.selection.getNode());
      content = this.getSelectionContent();
    }

    this.editor.insertContent(`
      <${this.tag} class='${this.className}'>${content}</${this.tag}>
    `);
  }

  getSelectionContent() {
    return this.editor.selection.getContent();
  }

  editorToggleClass() {
    if (this.editor.dom.hasClass("tinymce", this.toggleClass)) {
      this.editor.dom.removeClass("tinymce", this.toggleClass);
    } else {
      this.editor.dom.addClass("tinymce", this.toggleClass);
    }
  }
}

const buttons = {
  h1: new ButtonTinyMCE({
    text: "h1",
    onclick: "mceToggleFormat",
    tag: "h1"
  }),
  h2: new ButtonTinyMCE({
    text: "h2",
    onclick: "mceToggleFormat",
    tag: "h2"
  }),
  h3: new ButtonTinyMCE({
    text: "h3",
    onclick: "mceToggleFormat",
    tag: "h3"
  }),
  h4: new ButtonTinyMCE({
    text: "h4",
    onclick: "mceToggleFormat",
    tag: "h4"
  }),
  blockQuote: new ButtonTinyMCE({
    tooltip: "Цитата",
    onclick: "mceBlockQuote",
    icon: "blockquote"
  }),
  divider: new ButtonTinyMCE({
    onclick: "tagWithClass",
    tag: "hr",
    icon: "hr",
    tooltip: "Добавить черту",
    className: "v-divider theme--light"
  }),
  showCol: new ButtonTinyMCE({
    tooltip: "Показать/скрыть колонки",
    icon: "template",
    onclick: "editorToggleClass",
    toggleClass: "hm-hide-hint"
  })
};

export default buttons;
