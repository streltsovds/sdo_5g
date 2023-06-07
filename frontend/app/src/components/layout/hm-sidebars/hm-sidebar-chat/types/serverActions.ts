/**
 * Список названий экшенов для связи с сокет-сервером
 * @TODO Сделать рефактор — неочевидные константы
 */
import { FLAGS } from '../lib/const';

/**
 * Флаги доступа
 * @type {Flag}
 */
const { ANY } = FLAGS;

/**
 * Константы серверных экшeнов, получение с сервера
 * @see "./listen.ts"
 * @type {Object}
 */
export const serverActionNames = {
  ONLINE: 'online',
  CONNECTED: 'connected',
  DISCONNECTED: 'disconnected',
  SEND: 'send',
  SENT: 'sent',
  EDIT: 'edit',
  EDITED: 'edited',
  DELETE: 'delete',
};

/**
 * Константы клиентских экшeнов, отправка на сервер
 * @see "./push.ts"
 * @type {Object}
 */
export const clientActionNames = {
  CONNECTED: 'connected',
  SEND: 'send',
  EDIT: 'edit',
  DELETE: 'delete',
};

/**
 * Список флагов доступа клиентских экшнов
 * @type    {Object}
 * @default ANY
 */
export const actionsAccessList: { [action: string]: number } = new Proxy(
  {
    [clientActionNames.SEND]: ANY,
  },
  {
    // Прокси для фоллбка неизвестного экшна
    // TODO https://github.com/microsoft/TypeScript/issues/1863
    // `PropertyKey = string | number | symbol`
    // get(target: { [action: string]: Flag }, name: PropertyKey) {
    get(target: { [action: string]: number }, name: string | number) {
      return target.hasOwnProperty(name) ? target[name] : ANY;
    },
  }
);
