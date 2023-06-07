import * as docx from "docx";

export default {
  methods: {
    createDoc(data, type) {
      let doc = new docx.Document();
      if (type === "table") {
        let table = doc.createTable(data.data.length + 1, data.headers.length);
        data.headers.map((header, index) => {
          let cell = table.getCell(0, index);
          cell.addContent(new docx.Paragraph(header));
        });
        data.data.map((rowData, rowIndex) => {
          rowData.map((data, index) => {
            table
              .getCell(rowIndex + 1, index)
              .addContent(new docx.Paragraph(data));
          });
        });
      }
      return doc;
    },
    downloadDoc(doc) {
      let packer = new docx.Packer();
      packer.toBuffer(doc).then(buffer => {
        let blob = new Blob([buffer], { type: "octet/stream" });
        let a = document.createElement("a");
        document.body.appendChild(a);
        a.style = "display: none";
        let url = window.URL.createObjectURL(blob);
        a.href = url;
        a.download = `${document.title}.docx`;
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
      });
    }
  }
};
