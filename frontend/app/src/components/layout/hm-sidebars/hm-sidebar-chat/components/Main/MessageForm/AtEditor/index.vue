<template>
  <div class="atwho-wrap"
       ref="wrap"
       @input="handleInput"
       @keydown.capture="handleKeyDown"
  >
    <div class="atwho-panel"
         v-if="atwho && atwho.list.length > 0 && atwho.offset !== -1"
         :style="style"
    >
      <div class="atwho-inner"
           @select.prevent="()=> false"
      >
        <div class="atwho-view">
          <ul class="atwho-ul">
            <li
              v-for="(item, index) in atwho.list"
              :key="index"
              :class="atwhoLiClasses(item, index)"
              :ref="isCur(index) && 'cur'"
              :data-index="index"
              @mouseenter.prevent="handleItemHover"
              @click.prevent="handleItemClick"
            >
              <div class="atwho-item">
                <div class="atwho-item-avatar" v-if="!!item.avatarUrl">
                  <img :src="item.avatarUrl">
                </div>
                <span>
                  {{ itemName(item) }}
                </span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <span v-show="false" v-if="!!currentItem">
      <span ref="embeddedItem" :data-type="curDataType" :data-item-id="currentItem.id || ''">
        {{ curAt + currentItem.name }}
      </span>
    </span>

    <slot />
  </div>
</template>

<script>
import {
  closest, getOffset, getPrecedingRange,
  getRange, applyRange,
  getAtAndIndex, saveSelection, restoreSelection
} from './util'
import Vue from 'vue';
import HmRecruitersChatApi from '../../../../lib/api/index';
import { isEqual, range } from 'lodash';
import { AtDataType, PRVN , AtTypes, AtWhoType, NewRangeData, altAtsType, altAts} from './types';
import Candidate from '../../../../types/candidate';
import Recruiter from '../../../../types/recruiter';
import Vacancy from '../../../../types/vacancy';
import { PropType } from 'vue';
import { ItemTypes, ItemTypeByAt } from '../../../../types/itemTypes';
import getAltAt from '../../../../utils/getAltAt';

const nophoto = '/images/content-modules/nophoto.gif';

export default Vue.extend({
  name: 'At',
  props: {
    value: {
      type: String,
      default: null
    },
    ats: {
      type: Array ,
      default: () => ['@','"']
    },
    suffix: {
      type: String,
      default: ' '
    },
    hoverSelect: {
      type: Boolean,
      default: true
    },
    api: {
      type: Object,
      required: true
    }
  },

  data () {
    return {
      bindsValue: this.value != null,
      atwho: null,
      members: {
        '#': [],
        '!': [],
        '@': [],
        '$': []
      },
      isTyping: false,
      typingTimeoutId: null,
      lastReq: () => false,
      curAt: null,
      lastIsInsertItem: false,
      withoutRegExpAction: true,
    }
  },
  computed: {
    atItems () {
      return this.ats
    },
    curDataType () {
      if(!this.curAt) return '';
      return ItemTypeByAt[this.curAt];
    },
    currentItem () {
      if (this.atwho) {
        return this.atwho.list[this.atwho.cur];
      }
      return null;
    },
    style () {
      const selection = window.getSelection();
      if (selection && this.atwho) {
        let el = this.$el.querySelector('[contenteditable]');
        if(!el) return;
        const oRange = selection.getRangeAt(0);
        const oRect = oRange.getBoundingClientRect();
        if(!oRect) return;
        const containerRect = el.getBoundingClientRect();
        const topParam = document.body.clientWidth > 1386 ? 40 : -20;
        const left = (oRect.left - containerRect.left) + 'px'
        const top = oRect.top + window.scrollY - topParam + 'px';

        return { left, top }
      }
      return {}
    }
  },
  watch: {
    isTyping(value, oldValue){
      if(!value && oldValue && !this.lastIsInsertItem){
        this.lastReq();
        // if(!this.atwho){
        //   this.handleRegExp();
        // }
      }
    },
    members(value, oldValue){
      if(!isEqual(value, oldValue)){
        this.openPanelWith();
      }
    },
  },
  methods: {
    atwhoLiClasses(item, index){
      let classes = ['atwho-li'];
      item.isCreate? classes.push('atwho-li--create') : false;
      this.isCur(index) && classes.push('atwho-cur');
      return classes.join(' ');
    },
    filterMatch (name, chunk, at) {
      if(!name) return false;
      return name.toLowerCase()
        .indexOf(chunk.toLowerCase()) > -1
    },
    deleteMatch (name, chunk, suffix) {
      return chunk === name + suffix
    },
    itemName (v) {
      return v['name'];
    },
    isCur (index) {
      if(!this.atwho) return false;
      return index === this.atwho.cur
    },
    wrapTextInSpan(n, regExp, spanAttrs = ''){

      if(n.nodeType === Node.TEXT_NODE){
        const domParser = new DOMParser();
        let textNode = n;
        textNode.data = textNode.data.replace(regExp, `<span ${spanAttrs} contenteditable="false">$1</span>`);

        const fragment = document.createElement('template');
        fragment.innerHTML = textNode.data;

        textNode.replaceWith(...fragment.content.childNodes);

        return textNode;
      }
      if(n.nodeName === "DIV"){
        n.childNodes.forEach((node) => this.wrapTextInSpan(node, regExp, spanAttrs));
      }
      return n;
    },
    handleRegExp(){
      if(this.withoutRegExpAction) return;
      let el = this.$el.querySelector('[contenteditable]');

      if(!el) return;
      const savedSelection = saveSelection(el);

      const childNodes = el.childNodes;

      // Emails
      childNodes.forEach((n) => this.wrapTextInSpan(n, /([\w.\-\pL]+@\w+\.\w+)/g, `data-type="${ItemTypes.TYPE_CANDIDATE_EMAIL}"`));

      // Numbers
      childNodes.forEach((n) => this.wrapTextInSpan(n, /((\+7|7|8)+([0-9]){10})/gm, `data-type="${ItemTypes.TYPE_CANDIDATE_PHONE}"`));

      restoreSelection(el, savedSelection);
    },
    handleItemHover (e) {
      if (this.hoverSelect) {
        this.selectByMouse(e)
      }
    },
    handleItemClick (e) {
      this.selectByMouse(e)
      this.insertItem()
    },
    handleKeyDown (e) {
      const { atwho } = this
      if (atwho) {
        if (e.keyCode === 38 || e.keyCode === 40) { // ↑/↓
          if (!(e.metaKey || e.ctrlKey)) {
            e.preventDefault()
            e.stopPropagation()
            this.selectByKeyboard(e)
          }
          return
        }
        if (e.keyCode === 13) { // enter
          this.insertItem()
          e.preventDefault()
          e.stopPropagation()
          return
        }
        if (e.keyCode === 27) { // esc
          this.closePanel()
          return
        }
      }
    },
    async typingTimeout(timer = 500){
      if(this.typingTimeoutId){
        clearTimeout(this.typingTimeoutId);
      }
      this.typingTimeoutId = setTimeout(()=>{
        this.isTyping = false;
      },timer)
    },
    openPanelWith(){
      const range = getPrecedingRange()

      if (range) {
        const { atItems, members, itemName, filterMatch } = this;
        const text = range.toString()
        const atAndIndex = getAtAndIndex(text, atItems);
        // const at: AtTypes =atAndIndex.at;
        const at = (atAndIndex.at);
        const index = atAndIndex.index;

        const chunk = text.slice(index + at.length, text.length)
        if(!chunk) return;
        if(!members[at] || !members[at].length) return;

        const matched = members[at].filter((v) => {
          const name = itemName(v)
          return filterMatch(name, chunk, at)
        })
        this.openPanel(matched, range, index, at)
      }
    },
    async getNewMembers(chunk, at){
      const resp = await HmRecruitersChatApi.getMessageItems(at, chunk, this.api.lesson_id, this.api.subject_id);

      let tempMembers = [
        ...resp.items,
      ];

      this.members = {
        ...this.members,
        [at]: tempMembers
      };
    },
    handleInput (event) {
      if(event){
        const inputType = event.inputType;
        if(['insertLineBreak','deleteContentBackward'].includes(inputType)){
          this.withoutRegExpAction = true;
          return;
        }
      }

      this.withoutRegExpAction = false;
      this.lastIsInsertItem = false;
      this.isTyping = true;
      this.typingTimeout(750);

      const el = this.$el.querySelector('[contenteditable]');

      if(!el) return;

      this.$emit('input', el.innerHTML)

      const range = getPrecedingRange();

      if (range) {
        const { atItems } = this

        let show = true
        const text = range.toString()

        const atAndIndex = getAtAndIndex(text, atItems);
        const at = getAltAt(atAndIndex.at);
        const index = atAndIndex.index;
        const isCandidate = String(ItemTypeByAt[at]) === ItemTypes.TYPE_CANDIDATE;
        this.curAt = at;

        if (index < 0) show = false;

        const prev = text[index - 1];

        const chunk = text.slice(index + at.length, text.length);
        if(!chunk) show = false;
        if(!!chunk && chunk.trimStart().length !== chunk.length) show = false;

        if (!show) {
          this.closePanel()
        } else {
          const { filterMatch, itemName, members } = this
          let matched = [];

          this.lastReq = this.getNewMembers.bind(this, chunk, at);

          matched = (members[at] || []).filter((v) => {
            const name = itemName(v)
            return filterMatch(name, chunk, at)
          })


          if(!!event && isCandidate){
            (matched).push({
              name: chunk,
              avatarUrl: nophoto,
              isCreate: true
            });
          }

          show = false

          if (matched.length) {
            show = true
          }

          if (show) {
            this.openPanel(matched, range, index, at)
          } else {
            this.closePanel()
          }
        }
      }
    },

    closePanel () {
      this.atwho = null
    },
    openPanel (list, range, offset, at) {
      const fn = () => {
        const r = range.cloneRange()
        // r.setStart(r.endContainer, offset + at.length)
        const rect = r.getClientRects()[0]
        this.atwho = {
          range,
          offset,
          list: list || [],
          cur: 0 ,
        }
      }
      if (this.atwho) {
        fn()
      } else {
        setTimeout(fn, 10)
      }
    },
    selectByMouse (e) {
      const el = closest(e.target, (d) => {
        return d.getAttribute('data-index')
      })
      const cur = +el.getAttribute('data-index')

      if(!this.atwho) return;

      this.atwho = {
        ...this.atwho,
        cur
      }
    },
    selectByKeyboard (e) {
      const offset = e.keyCode === 38 ? -1 : 1

      if(!this.atwho) return;

      const { cur, list } = this.atwho;
      const nextCur = list.length > cur ? cur + 1: 0;

      this.atwho = {
        ...this.atwho,
        cur: nextCur
      }
    },
    insertHtml (html, r) {
      r.deleteContents()
      const node = r.endContainer
      const outerHTMLElement = this.htmlToElement(html);
      if(!outerHTMLElement) return;
      var newElement = document.createElement('span')

      let newEl = this.htmlToElement(outerHTMLElement.innerHTML);
      const dataType = outerHTMLElement.getAttribute('data-type') || '';
      const dataItemId = outerHTMLElement.getAttribute('data-item-id') || '';

      if(newEl){
        newElement.appendChild(newEl);
        newElement.setAttribute('data-type', dataType);
        newElement.setAttribute("data-item-id", dataItemId);
        newElement.setAttribute("contenteditable", 'false');
      }


      if (node.nodeType === Node.TEXT_NODE) {
        const cut = r.endOffset
        var secondPart = (node).splitText(cut);
        const parentNode = node.parentNode;

        if(!parentNode) return;

        parentNode.insertBefore(newElement, secondPart);
        r.setEndBefore(secondPart)
      } else {
        const { suffix } = this;
        const t = document.createTextNode(suffix)
        const tNode = document.createTextNode('');
        // r.insertNode(newElement)
        // r.setEndAfter(newElement)
        // r.insertNode(t)
        // r.setEndAfter(t)
      }


      r.collapse(false)
      applyRange(r)

    },

    insertItem () {
      const { range, offset, list, cur } = this.atwho;
      const { suffix, atItems, itemName } = this
      const r = range.cloneRange()
      const text = range.toString()
      const atAndIndex = getAtAndIndex(text, atItems);
      const at = getAltAt(atAndIndex.at);
      const index = atAndIndex.index;


      r.setStart(r.endContainer, index)
      applyRange(r)
      applyRange(r)

      const curItem = list[cur];

      const embeddedItem = this.$refs.embeddedItem;
      if(embeddedItem){
        const html = embeddedItem.outerHTML + ' ';
        this.insertHtml(html, r);
      }


      this.$emit('insert', curItem)
      this.handleInput(null)
      this.lastIsInsertItem = true;
    },
    htmlToElement (html) {
      var template = document.createElement('template');
      html = html.trim();
      template.innerHTML = html;
      return template.content.firstChild;
    }
  }
});
</script>

<style lang="sass" src="./index.sass"></style>

