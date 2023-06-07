import XLSX from "xlsx";

export default {
  methods: {
    createXlsx(data, type) {
      let wb = XLSX.utils.book_new();
      wb.SheetNames.push("Лист 1");
      let ws_data = [];
      let ws;
      if (type === "table") {
        ws_data.push(data.headers);
        data.data.map(userData => {
          ws_data.push(userData);
        });
        ws = XLSX.utils.aoa_to_sheet(ws_data);
      }

      wb.Sheets["Лист 1"] = ws;
      let wbout = XLSX.write(wb, { bookType: "xlsx", type: "binary" });

      /* s2ab - string to array buffer */
      function s2ab(s) {
        let buf = new ArrayBuffer(s.length);
        let view = new Uint8Array(buf);
        for (let i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xff;
        return buf;
      }

      return new Blob([s2ab(wbout)], { type: "octet/stream" });
    },
    downloadXlsx(blob) {
      let a = document.createElement("a");
      document.body.appendChild(a);
      a.style = "display: none";
      let url = window.URL.createObjectURL(blob);
      a.href = url;
      a.download = `${document.title}.xlsx`;
      a.click();
      window.URL.revokeObjectURL(url);
      a.remove();
    }
  }
};
