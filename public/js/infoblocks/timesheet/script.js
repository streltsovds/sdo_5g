'use strict';
(function () {
  window.hm.timesheet = {} // инициализируем модуль
  var _this = window.hm.timesheet

  var rootReducer = Redux.combineReducers({
    state: mainStateReducer,
    lastAction: lastAction,
  })

  var store = Redux.createStore(rootReducer)

  function lastAction(state, action) {
    return action
  }

  function mainStateReducer(state, action) {
    var __assign =
      (this && this.__assign) ||
      Object.assign ||
      function (t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i]
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p]
        }
        return t
      }

    if (state === void 0) {
      state = {}
    }

    // state = {
    //   items: [],
    // }

    //state.items = state.items ? state.items : []

    switch (action.type) {
      case 'POPULATE_STORE':
        if (action.payload[0]) {
          return __assign({}, state, {
            items: action.payload,
          })
        } else {
          return state
        }

      case 'ADD_ITEM':
        if (
          state.items &&
          compareItems(state.items[state.items.length - 1], action.payload)
        ) {
          hm.core.Console.log('same item')
          return state
        } else if (Array.isArray(state.items)) {
          return __assign({}, state, state.items.push(action.payload))
        } else {
          return __assign({}, state, {
            items: [action.payload],
          })
        }
      case 'REMOVE_ITEM':
        return __assign({}, state, {
          items: state.items.filter(function (x) {
            return x.index !== action.payload
          }),
        })
      case 'REMOVE_DELETEABLE':
        return __assign({}, state, {
          items: state.items.map(function (x) {
            if (x.isDeleteable) {
              delete x.isDeleteable
            }
            return x
          }),
        })
      case 'TOGGLE_BUTTON':
        return __assign({}, state, {
          buttonState: action.payload,
        })

      default:
        return state
    }
  }

  function populateStore(data) {
    return {
      type: 'POPULATE_STORE',
      payload: data,
    }
  }

  function addItem(data) {
    store.dispatch({
      type: 'ADD_ITEM',
      payload: data,
    })
  }

  function removeItem(data) {
    store.dispatch({
      type: 'REMOVE_ITEM',
      payload: data,
    })
  }

  _this.getData = function (url) {
    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'json',
      success: function (data) {
        store.dispatch(populateStore(data))
        toggleButtonsStateAction({
          type: 'TOGGLE_BUTTON',
          payload: {
            className: '.timesheet__savebtn',
            isDisabled: true,
          },
        })
        toggleButtonsStateAction({
          type: 'TOGGLE_BUTTON',
          payload: {
            className: '.timesheet__addBtn',
            isDisabled: true,
          },
        })
      },
    })
  }
  // var state = store.getState().reducer

  function logChanges() {
    hm.core.Console.log('Last reducer - ', store.getState().lastAction)
    hm.core.Console.log('Store changed!', store.getState().state)
  }

  function compareItems(itemOne, itemTwo) {
    var returnvalue = []

    for (var item in itemOne) {
      if (item === 'isDeleteable') {
        continue
      }
      if (item === 'typeId') {
        continue
      }

      if (_.isEqual(itemOne[item], itemTwo[item])) {
        returnvalue.push(true)
      } else {
        returnvalue.push(false)
      }
    }
    return returnvalue.every(function (x) {
      return x
    })
  }

  _this.render = function () {
    if (store.getState().lastAction.type === 'TOGGLE_BUTTON') return

    var items = store.getState().state.items
    var container = document.querySelector('.timesheet__content-wrapper')
    var contentWrapper = document.querySelector('.timesheet__content-wrapper')
    if (!items) {
      contentWrapper.textContent = 'Записей нет. Вы можете добавить запись ниже'
    } else if (items[0]) {
      var container = contentWrapper
      container.textContent = ''
      items.forEach(function (item, i) {
        var template = document
          .querySelector('#template')
          .content.cloneNode(true)

        template.querySelector('.timesheet__item--filled').dataset.typeId =
          item.i
        template.querySelector('.timesheet__select--filled').textContent =
          item.type

        template.querySelector('.timesheet__main-input').textContent =
          item.description
        template.querySelector('.timesheet__time-from span').textContent =
          item.time.from
        template.querySelector('.timesheet__time-to span').textContent =
          item.time.to
        if (!item.isDeleteable) {
          template.querySelector('.timesheet__deleteBtn').style.display = 'none'
        } else {
          template
            .querySelector('.timesheet__deleteBtn')
            .addEventListener('click', function () {
              removeItem(item.index)
            })
        }
        container.appendChild(template)
        var condition = items.some(function (item) {
          return item.isDeleteable
        })
        if (!condition) {
          toggleButtonsStateAction({
            type: 'TOGGLE_BUTTON',
            payload: {
              className: '.timesheet__savebtn',
              isDisabled: true,
            },
          })
        }
        // document.querySelector('#timesheetBlock').parentElement.style.height =
        //   'auto' // триггерим изменение высоты виджета
      })
    }
  }
  _this.handleFormSubmit = function (formID, timeErrorClass, timeClass) {
    var forma = document.querySelector(formID)
    var addBtn = document.querySelector(formID + ' button')
    forma.addEventListener('input', function (e) {
      e.target.classList.remove('valid')
      toggleButtonsStateAction({
        type: 'TOGGLE_BUTTON',
        payload: {
          className: '.timesheet__addBtn',
          isDisabled: false,
        },
      })
    })
    // addBtn.addEventListener('click', function (e) {

    // })
    forma.addEventListener('submit', function (e) {
      e.preventDefault()
      hm.core.Console.log(e)
      var inputs = forma.querySelectorAll('input')
      var selecta = forma.querySelector('select')
      selecta.classList.remove('valid')
      for (var el in inputs) {
        if (inputs[el] && inputs[el].classList) {
          inputs[el].classList.remove('valid')
        }
      }
      if (e.target[2].value > e.target[3].value) {
        document.querySelector(timeErrorClass).hidden = ''
        setTimeout(function () {
          document.querySelector(timeErrorClass).hidden = true
        }, 4000)

        document.querySelector(timeClass).classList.add('invalid')

        e.target[2].onchange = function () {
          document.querySelector(timeClass).classList.remove('invalid')
          document.querySelector(timeErrorClass).hidden = true
        }

        e.target[2].onchange = function () {
          document.querySelector(timeClass).classList.remove('invalid')
          document.querySelector(timeErrorClass).hidden = true
        }

        return
      }
      var newId =
        store.getState().state.items !== undefined ?
        store.getState().state.items.length + 1 :
        1
      addItem({
        description: e.target[1].value,
        type: e.target[0].options[e.target[0].selectedIndex].innerText,
        time: {
          from: e.target[2].value,
          to: e.target[3].value,
        },
        typeId: e.target[0].value,
        isDeleteable: true,
        index: newId,
      })
      e.target.reset()
      toggleButtonsStateAction({
        type: 'TOGGLE_BUTTON',
        payload: {
          className: '.timesheet__addBtn',
          isDisabled: true,
        },
      })
      for (var el in e.target) {
        if (e.target[el] && e.target[el].classList) {
          e.target[el].classList.add('valid')
        }
      }
      toggleButtonsStateAction({
        type: 'TOGGLE_BUTTON',
        payload: {
          className: '.timesheet__savebtn',
          isDisabled: false,
        },
      })
    })
  }

  _this.handleSave = function (url) {
    var data2send = store
      .getState()
      .state.items.filter(function (item) {
        return item.isDeleteable
      })
      .map(function (item) {
        return {
          typeId: item.typeId,
          type: item.type,
          description: item.description,
          time: item.time,
        }
      })

    function onSuccess() {
      store.dispatch({
        type: 'REMOVE_DELETEABLE',
      })
      toggleButtonsStateAction({
        type: 'TOGGLE_BUTTON',
        payload: {
          className: '.timesheet__savebtn',
          isDisabled: true,
        },
      })
      var url = 'infoblock/timesheet/get-data/format/json'
      var container = 'timesheet-chart-container'
      loadData(url, container)
      // $.ajax({
      //   type: 'GET',
      //   url: 'infoblock/timesheet/get-data/format/json',
      //   dataType: 'json',
      //   success: function(data) {
      //     hm.core.Console.log(data)
      //     hm.timesheet.chart.dataProvider = data.data
      //     hm.timesheet.chart.validateData()
      //   },
      // })
    }
    $.ajax({
      type: 'POST',
      url: url,
      data: JSON.stringify(data2send),
      success: onSuccess,
    })
  }

  // var logging = store.subscribe(logChanges)
  // //logging()
  store.subscribe(_this.render)

  // function getChartData(params) {
  //   var items = store.getState().state.items
  //   var data = [{}]

  //   items.forEach(function (item) {
  //     data[]
  //   }
  // }

  function toggleButtonsStateListener() {
    var type = store.getState().lastAction.type
    if (type !== 'TOGGLE_BUTTON') return

    var payload = store.getState().lastAction.payload
    var className = payload.className
    var isDisabled = payload.isDisabled ? true : ''
    document.querySelector(className).disabled = isDisabled
  }

  function toggleButtonsStateAction(action) {
    if (_.isEqual(store.getState().state.buttonState, action.payload)) {
      return
    } else {
      store.dispatch(action)
    }
  }

  store.subscribe(toggleButtonsStateListener)

  function addTabIndexes() {
    if (window.screen.width < 1300) {
      var container = document.querySelector('.timesheet__item-tofill')
      container.children[0].tabIndex = '1'
      var inputs = container.querySelectorAll('.timesheet__item-tofill input')
      inputs[0].tabIndex = '4'
      inputs[1].tabIndex = '2'
      inputs[2].tabIndex = '3'
      container.querySelector('button').tabIndex = '5'
    } else {
      var container = document.querySelector('.timesheet__item-tofill')
      container.children[0].tabIndex = '0'
      var inputs = container.querySelectorAll('.timesheet__item-tofill input')
      inputs[0].tabIndex = '0'
      inputs[1].tabIndex = '0'
      inputs[2].tabIndex = '0'
      container.querySelector('button').tabIndex = '0'
    }
  }
  $(addTabIndexes)
  $(window).resize(addTabIndexes)
})()