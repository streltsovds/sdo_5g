export default {
  methods: {
    createPrintWindow(data, type) {
      let printWindow = window.open("", "PRINT");
      printWindow.document.write(
        "<html><head><title>" + document.title + "</title>"
      );
      printWindow.document.write("</head><body >");
      if (type === "table") {
        printWindow.document.write(
          "<h1>" + document.title + '</h1><table border="1" cellpadding="3">'
        );
        let headers = "";
        data.headers.map(header => {
          headers += `<th>${header}</th>`;
        });
        printWindow.document.write("<tr>" + headers + "</tr>");
        let tableData = "";
        data.data.map(rowData => {
          tableData += "<tr>";
          rowData.map(data => {
            tableData += `<th>${data}</th>`;
          });
          tableData += "</tr>";
        });
        printWindow.document.write(tableData);
        printWindow.document.write("</table></body></html>");
      }
      printWindow.document.close(); // обязателен для IE >= 10
      printWindow.focus(); // обязателен для IE >= 10*/
      printWindow.print();
      printWindow.close();
    }
  }
};
