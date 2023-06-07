import rangy from 'rangy';
export function applyRange(range) {
  const selection = window.getSelection();
  if (selection) {
    // 容错
    selection.removeAllRanges();
    selection.addRange(range);
  }
}
export function getRange() {
  const selection = window.getSelection();
  if (selection && selection.rangeCount > 0) {
    return selection.getRangeAt(0);
  }
}

export function getAtAndIndex(text, ats) {
  return ats
    .map(at => {
      return { at, index: text.lastIndexOf(at) };
    })
    .reduce((a, b) => {
      return a.index > b.index ? a : b;
    });
}

/* eslint-disable */
// http://stackoverflow.com/questions/26747240/plain-javascript-replication-to-offset-and-position
export function getOffset(element, target) {
    // var element = document.getElementById(element),
    //     target  = target ? document.getElementById(target) : window;
    target = target || window
    var offset = {top: element.offsetTop, left: element.offsetLeft},
        parent = element.offsetParent;
    while (parent != null && parent != target) {
       offset.left += parent.offsetLeft;
       offset.top  += parent.offsetTop;
       parent = parent.offsetParent;
    }
    return offset;
}
// http://stackoverflow.com/questions/3972014/get-caret-position-in-contenteditable-div
export function closest (el, predicate) {
  /* eslint-disable */
  do if (predicate(el)) return el;
  while (el = el && el.parentNode);
}
// http://stackoverflow.com/questions/15157435/get-last-character-before-caret-position-in-javascript
// 修复 "空格+表情+空格+@" range报错 应设(endContainer, 0)
// stackoverflow上的这段代码有bug
export function getPrecedingRange() {
  const r = getRange()
  if (r) {
    const range = r.cloneRange()
    range.collapse(true)
    // var el = closest(range.endContainer, d => d.contentEditable)
    // range.setStart(el, 0)
    range.setStart(range.endContainer, 0)
    return range
  }
}
/* eslint-enable */

export function saveSelection(containerEl) {
  var charIndex = 0,
    start = 0,
    end = 0,
    foundStart = false,
    stop = {};
  var sel = rangy.getSelection(),
    range;

  function traverseTextNodes(node, range) {
    if (node.nodeType == 3) {
      if (!foundStart && node == range.startContainer) {
        start = charIndex + range.startOffset;
        foundStart = true;
      }
      if (foundStart && node == range.endContainer) {
        end = charIndex + range.endOffset;
        throw stop;
      }
      charIndex += node.length;
    } else {
      for (var i = 0, len = node.childNodes.length; i < len; ++i) {
        traverseTextNodes(node.childNodes[i], range);
      }
    }
  }

  if (sel.rangeCount) {
    try {
      traverseTextNodes(containerEl, sel.getRangeAt(0));
    } catch (ex) {
      if (ex != stop) {
        throw ex;
      }
    }
  }

  return {
    start: start,
    end: end,
  };
}

export function restoreSelection(containerEl, savedSel) {
  var charIndex = 0,
    range = rangy.createRange(),
    foundStart = false,
    stop = {};
  range.collapseToPoint(containerEl, 0);

  function traverseTextNodes(node) {
    if (node.nodeType == 3) {
      var nextCharIndex = charIndex + node.length;
      if (
        !foundStart &&
        savedSel.start >= charIndex &&
        savedSel.start <= nextCharIndex
      ) {
        range.setStart(node, savedSel.start - charIndex);
        foundStart = true;
      }
      if (
        foundStart &&
        savedSel.end >= charIndex &&
        savedSel.end <= nextCharIndex
      ) {
        range.setEnd(node, savedSel.end - charIndex);
        throw stop;
      }
      charIndex = nextCharIndex;
    } else {
      for (var i = 0, len = node.childNodes.length; i < len; ++i) {
        traverseTextNodes(node.childNodes[i]);
      }
    }
  }

  try {
    traverseTextNodes(containerEl);
  } catch (ex) {
    if (ex == stop) {
      const parent = range.startContainer.parentElement;

      if (parent && parent.nodeName === 'SPAN') {
        range.setStartAfter(parent);
        range.collapse(true);
      }

      rangy.getSelection().setSingleRange(range);
    } else {
      throw ex;
    }
  }
}
