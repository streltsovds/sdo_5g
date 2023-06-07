/**
 * Функционал для одключения к сокет-серверу для обмена данными
 */

import { clientActionNames } from '../../types/serverActions';
import { Store } from 'vuex';
import HmRecruitersChatState from '../../vuexStoreModule/state';
import ActionTypesList from '../actionTypesList';

/**
 * Объект с соединением
 * @type {WebSocket}
 */
let socketConnection;

/**
 * Последнее состояние соединения
 * @type {number}
 */
let lastReadyState = 0;

/**
 * Устанавливает соединения
 * @exports
 * @param  {string}  host    — Адрес для соединения
 * @return {Promise}
 */
export async function createConnection(
  host,
  sessionId,
  namespace,
  store
) {
  return new Promise((resolve, reject) => {
    socketConnection = new WebSocket(host);
    lastReadyState = socketConnection.readyState;

    socketConnection.onopen = () => {
      lastReadyState = socketConnection.readyState;

      action(clientActionNames.CONNECTED, {
        namespace: namespace,
        sessionId: sessionId,
      });

      resolve();
    };

    socketConnection.onclose = () => {
      const curReadyState = socketConnection.readyState;
      const isOpened = curReadyState === WebSocket.OPEN;
      const wasOpened = lastReadyState === WebSocket.OPEN;

      if (wasOpened && !isOpened) {
        store.commit(ActionTypesList.THROW_FATAL_ERROR, { errorCode: 0 });
      }
    };

    socketConnection.onerror = () => {
      const STATE_OPEN = WebSocket.OPEN;

      const curReadyState = socketConnection.readyState;
      const wasOpened = lastReadyState === STATE_OPEN;
      const isOpened = curReadyState === STATE_OPEN;

      if (!wasOpened && !isOpened) {
        // Соединение не было установлено
        reject(`Не удалось подключитсья к сокет-серверу`);
      }
    };
  });
}
/**
 * Обрабатывает входящие собщения
 * @param {Function} handler — обработчик
 */
export function registerMessageListener(
  listener,
  store
) {
  socketConnection.onmessage = event => {
    listener(event, store);
  };
}

/**
 * Отправляет действие на сервер
 * @param {string} action — ID действия
 * @param {any}    data   — Сообщение
 */
export function action(action, data) {
  let stringData = JSON.stringify({ action, data });
  send(stringData);
}

/** Принудительно разрывает соединение */
export function disconnect() {
  socketConnection.close();
}

/**
 * Отправляет сообщение
 * @private
 * @param {*} data — объект для отпарвки
 */
function send(data) {
  // Соединения не существует
  if (socketConnection == null) {
    return;
  }

  // Метод для отправки недоступен
  if (socketConnection.send == null) {
    return;
  }

  // Если соединение не установлено,
  // отправляем сообщение при открытии соединения
  if (socketConnection.readyState === WebSocket.CONNECTING) {
    const openEventId = (socketConnection.onopen = () => {
      socketConnection.send(data);
      socketConnection.removeEventListener('open', openEventId);
    });
  } else {
    // Просто отправляем сообщение
    socketConnection.send(data);
  }
}
