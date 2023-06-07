<script>
// PDFDocument renders an entire PDF inline using
// PDF.js and <canvas>. Currently does not support,
// rendering of selected pages (but could be easily
// updated to do so).
import debug from 'debug';
const log = debug('app:components/PDFData');
import PDFJS from 'pdfjs-dist/build/pdf.js';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker.entry.js';
import range from 'lodash/range';

function getDocument(url) {
  // HM MOD BEGIN

  // Using import statement in this way allows Webpack
  // to treat pdf.js as an async dependency so we can
  // avoid adding it to one of the main bundles

  // Failed to construct 'Worker': Script at 'http://192.168.0.97:9000/756424761fc563346139.worker.js' cannot be accessed from origin 'http://5g-dev.d9k'.
  //   at new module.exports (webpack-internal:///./node_modules/worker-loader/dist/cjs.js!./node_modules/pdfjs-dist/build/pdf.worker.js:2:10)
  //   at eval (webpack-internal:///./node_modules/pdfjs-dist/webpack.js:21:42)
  //   at Object../node_modules/pdfjs-dist/webpack.js (http://192.168.0.97:9000/vendors~pdfjs-dist.js:22:1)
  //   at __webpack_require__ (http://192.168.0.97:9000/runtime.js:791:30)
  //   at fn (http://192.168.0.97:9000/runtime.js:151:20)
  //   at fn.t (http://192.168.0.97:9000/runtime.js:195:35)

  // return import(
  //   /* webpackChunkName: 'pdfjs-dist' */
  //   'pdfjs-dist/webpack').then(pdfjs => pdfjs.getDocument(url));

  PDFJS.GlobalWorkerOptions.workerSrc = pdfjsWorker;
  PDFJS.disableWorker = true;
  return PDFJS.getDocument(url);

  // END HM MOD
}

// pdf: instance of PDFData
// see docs for PDF.js for more info
function getPages(pdf, first, last) {
  const allPages = range(first, last+1).map(number => pdf.getPage(number));
  return Promise.all(allPages);
}

const BUFFER_LENGTH = 10;
function getDefaults() {
  return {
    pages: [],
    cursor: 0,
  };
}

export default {
  name: 'PDFData',

  props: {
    url: {
      type: String,
      required: true,
    },
  },

  data() {
    return Object.assign(getDefaults(), {
      pdf: undefined,
    });
  },

  watch: {
    url: {
      handler(url) {
        getDocument(url)
          .then(pdf => (this.pdf = pdf))
          .catch(response => {
            this.$emit('document-errored', {text: 'Failed to retrieve PDF', response});
            log('Failed to retrieve PDF', response);
          });
      },
      immediate: true,
    },

    pdf(pdf, oldPdf) {
      if (!pdf) return;
      if (oldPdf) Object.assign(this, getDefaults());

      this.$emit('page-count', this.pageCount);
      this.fetchPages();
    },
  },

  computed: {
    pageCount() {
      return this.pdf ? this.pdf.numPages : 0;
    },
  },

  methods: {
    fetchPages(currentPage = 0) {
      if (!this.pdf) return;
      if (this.pageCount > 0 && this.pages.length === this.pageCount) return;

      const startIndex = this.pages.length;
      if (this.cursor > startIndex) return;

      const startPage = startIndex + 1;
      const endPage = Math.min(Math.max(currentPage, startIndex + BUFFER_LENGTH), this.pageCount);
      this.cursor = endPage;

      log(`Fetching pages ${startPage} to ${endPage}`);
      getPages(this.pdf, startPage, endPage)
        .then((pages) => {
          const deleteCount = 0;
          this.pages.splice(startIndex, deleteCount, ...pages);
          return this.pages;
        })
        .catch((response) => {
          this.$emit('document-errored', {text: 'Failed to retrieve pages', response});
          log('Failed to retrieve pages', response);
        });
    },

    onPageRendered({text, page}) {
      log(text, page);
    },

    onPageErrored({text, response, page}) {
      log('Error!', text, response, page);
    },
  },

  created() {
    this.$on('page-rendered', this.onPageRendered);
    this.$on('page-errored', this.onPageErrored);
    this.$on('pages-fetch', this.fetchPages);
  },

  render(h) {
    return h('div', [
      this.$scopedSlots.preview({
        pages: this.pages,
      }),
      this.$scopedSlots.document({
        pages: this.pages,
      }),
    ]);
  },
};
</script>
