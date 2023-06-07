import htmlFormatting from "./htmlFormatting";

const headerRule = {
  br: {
    process: node => {
      let parent = node.parentNode,
        space = document.createTextNode(" ");

      parent.replaceChild(space, node);
    }
  }
};

const valid_elements = {
  h1: {
    convert_to: "h2",
    valid_styles: "",
    valid_classes: "",
    no_empty: true,
    valid_elements: headerRule
  },
  h2: {
    valid_styles: "",
    valid_classes: "",
    no_empty: true,
    valid_elements: headerRule,
    process: node => {
      node.classList.add("display-1");
    }
  },

  h3: {
    valid_styles: "",
    valid_classes: "",
    no_empty: true,
    valid_elements: headerRule,
    process: node => {
      node.classList.add("headline");
    }
  },
  h4: {
    valid_styles: "",
    valid_classes: "",
    no_empty: true,
    valid_elements: headerRule,
    process: node => {
      node.classList.add("title");
    }
  },
  p: {
    valid_styles: "text-align",
    valid_classes: "",
    no_empty: true
  },
  a: {
    valid_styles: "",
    valid_classes: "",
    no_empty: true,

    process: node => {
      let host = "http://" + window.location.host + "/";
      if (node.href.indexOf(host) !== 0) {
        node.target = "_blank";
      }
    }
  },
  img: {
    valid_styles: "",
    valid_classes: "",
    process: node => {
      node.classList.add("img-temp");
    }
  },
  br: {
    valid_styles: "",
    valid_classes: ""
  },
  "blockquote,b,strong,i,em,s,strike,sub,sup,kbd,ul,ol,li,dl,dt,dd,time,address,thead,tbody,tfoot": {
    valid_styles: "",
    valid_classes: "",
    no_empty: true
  },
  table: {
    valid_styles: "text-align,vertical-align",
    valid_classes: "",
    no_empty: true,
    process: node => {
      node.classList.add("v-datatable", "v-table", "theme--light", "hm-tiny-table-style");
    }
  },
  "tr,th,td": {
    valid_styles: "text-align,vertical-align",
    valid_classes: "",
    no_empty: true
  },
  "embed,iframe": {
    valid_classes: ""
  }
};

const pastePostProcess = function(plugin, args) {
  htmlFormatting(args.node, valid_elements);
};

export default pastePostProcess;
