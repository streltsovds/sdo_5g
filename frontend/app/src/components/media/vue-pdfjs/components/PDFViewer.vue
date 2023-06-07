<template>
  <div class="pdf-viewer">
    <header class="pdf-viewer__header">
      <div class="pdf-preview-toggle">
        <a
          @click.prevent.stop="togglePreview"
          class="icon"
        >
          <v-tooltip-simple :text="_('Список слайдов')">
            <svg-icon name="listItems" :color="colorButton" title="" />
          </v-tooltip-simple>
        </a>
      </div>

      <PDFZoom
        :scale="scale"
        @change="updateScale"
        @fit="updateFit"
        class="header-item"
        @dblclick="preventEvent"
      />

      <PDFPaginator
        v-model="currentPage"
        :pageCount="pageCount"
        class="header-item"
        />

      <slot name="header"></slot>
    </header>

    <PDFData
      class="pdf-viewer__main"
      :url="url"
      @page-count="updatePageCount"
      @page-focus="updateCurrentPage"
      @document-rendered="onDocumentRendered"
      @document-errored="onDocumentErrored"
      >
      <template v-slot:preview="{pages}">
        <PDFPreview
          v-show="isPreviewEnabled"
          class="pdf-viewer__preview"
          v-bind="{pages, scale, currentPage, pageCount, isPreviewEnabled}"
          />
      </template>

      <template v-slot:document="{pages}">
        <PDFDocument
          class="pdf-viewer__document"
          :class="{ 'preview-enabled': isPreviewEnabled }"
          v-bind="{pages, scale, optimalScale, fit, currentPage, pageCount, isPreviewEnabled}"
          @scale-change="updateScale"
          />
      </template>
    </PDFData>
  </div>
</template>

<script>
// import PreviewIcon from '../assets/icon-preview.svg';

import PDFDocument from './PDFDocument';
import PDFData from './PDFData';
import PDFPaginator from './PDFPaginator';
import PDFPreview from './PDFPreview';
import PDFZoom from './PDFZoom';
import SvgIcon from '@/components/icons/svgIcon';
import VTooltipSimple from "@/components/helpers/v-tooltip-simple";

function floor(value, precision) {
  const multiplier = Math.pow(10, precision || 0);
  return Math.floor(value * multiplier) / multiplier;
}

export default {
  name: 'PDFViewer',

  components: {
    VTooltipSimple,
    PDFDocument,
    PDFData,
    PDFPaginator,
    PDFPreview,
    PDFZoom,
    SvgIcon,
    // PreviewIcon,
  },

  props: {
    url: String,
  },

  data() {
    return {
      scale: undefined,
      optimalScale: undefined,
      fit: undefined,
      currentPage: 1,
      pageCount: undefined,
      isPreviewEnabled: false,
    };
  },

  computed: {
    colorButton() {
      return '#FFFFFF';
    },
  },

  methods: {
    onDocumentRendered() {
      this.$emit('document-errored', this.url);
    },

    onDocumentErrored(e) {
      this.$emit('document-errored', e);
    },

    preventEvent(e) {
      e.preventDefault();
    },

    updateScale({scale, isOptimal = false}) {
      const roundedScale = floor(scale, 2);
      if (isOptimal) this.optimalScale = roundedScale;
      this.scale = roundedScale;
    },

    updateFit(fit) {
      this.fit = fit;
    },

    updatePageCount(pageCount) {
      this.pageCount = pageCount;
    },

    updateCurrentPage(pageNumber) {
      this.currentPage = pageNumber;
    },

    togglePreview() {
      this.isPreviewEnabled = !this.isPreviewEnabled;
    },
  },

  watch: {
    url() {
      this.currentPage = undefined;
    },
  },

  mounted() {
    document.body.classList.add('overflow-hidden');
  },
};
</script>

<style lang="scss">
.pdf-viewer {
  &__header {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;

    box-sizing: border-box;
    padding: 1em;
    padding-bottom: 0.5rem;
    position: relative;
    min-height: 64px;

    /* z-index: 99; */
    /* background-color: #606f7b; */
    background-color: #4b4b4b;

    user-select: none;

    box-shadow: 0px 2.61143px 5.22287px rgba(0, 0, 0, 0.14), 0px 5.22287px 6.52858px rgba(0, 0, 0, 0.12), 0px 1.30572px 13.0572px rgba(0, 0, 0, 0.2);
  }
  .pdf-preview-toggle {
    margin-bottom: 0.5rem;
  }
  .header-item {
    margin: 0 2.5rem;
    margin-bottom: 0.5rem;
  }

  .pdf-viewer__main {
    display: flex;
    overflow: hidden;
  }

  /* .pdf-viewer__document, */
  /* .pdf-viewer__preview { */
  /*   top: 70px; */
  /* } */

  .pdf-viewer__preview {
    /*display: block;*/
    /*width: 15%;*/
    /*right: 85%;*/
    flex-basis: 15%;
    flex-grow: 0;
    flex-shrink: 0;
  }

  .pdf-viewer__document {
    /*top: 70px;*/
    /*width: 100%;*/
    /*left: 0;*/
    flex-grow: 1;
  }

  .pdf-viewer__document.preview-enabled {
    width: 85%;
    left: 15%;
  }

  .pdf-preview,
  .pdf-document {
    position: static;
  }

  /* HM MOD BEGIN */

  /* for .pdf-viewer */
  box-sizing: content-box;
  background-color: #606f7b;
  text-align: center;
  display: flex;
  flex-direction: column;
  overflow: hidden;

  /* Styles from app.js */

  a.icon {
    cursor: pointer;

    /* border: 1px #333 solid; */
    border: 1px #424242 solid;

    /* background: white; */
    background-color: #3D3D3D;

    color: #333;
    font-weight: bold;

    //padding: 6px;
    //height: 24px;
    //width: 24px;
    height: 34px;
    width: 34px;
    font-size: 24px;

    display: flex;
    justify-content: center;
    align-items: center;

    &:first-child {
      border-top-left-radius: 4px;
      border-bottom-left-radius: 4px;
    }

    &:last-child {
      border-top-right-radius: 4px;
      border-bottom-right-radius: 4px;
    }

    &:hover {
      background-color: #666666;
    }

    .v-tooltip-simple__activator {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  .box-shadow {
    box-shadow: 0 15px 30px 0 rgba(0, 0, 0, 0.11), 0 5px 15px 0 rgba(0, 0, 0, 0.08);
  }
  .overflow-hidden {
    overflow: hidden;
  }

  label.form {
    color: white;
    font-weight: bold;
    margin-bottom: 2rem;
    display: block;
  }
  input {
    min-width: 50px;
    padding: 5px 4px;
    font-size: 1rem;
    background-color: white;
    color: black;
  }

  /* END HM MOD */
}

.pdf-viewer-scrollbar {
  &::-webkit-scrollbar-track {
    background-color: transparent;
  }

  &::-webkit-scrollbar {
    width: 18px;
    height: 18px;
    background-color: transparent;
  }

  &::-webkit-scrollbar-thumb {
    border-radius: 9px;

    /** TODO grey-dark */
    background-color: #979797;
    border: 4px solid transparent;

    /** без этого не работает padding */
    background-clip: padding-box;

    &:hover {
      background-color: #b0b0b0;
    }
  }

  /** Без этих стилей, когда и вертикальная, и горизонтальная полоса прокрутки, между ними в углу будет белый квадрат */
  &::-webkit-scrollbar-corner {
    background-color: transparent;
  }
}

@media print {
  .pdf-viewer {
    header {
      display: none;
    }
  }
}
@media(max-width: 768px) {
  .pdf-viewer__header {
    justify-content: space-between;
  }
  .pdf-viewer .header-item {
    margin: 0 1rem;
    margin-bottom: 0.5rem;
  }
  .pdf-viewer .pdf-paginator {
    margin: 0 !important;
  }
}
</style>
