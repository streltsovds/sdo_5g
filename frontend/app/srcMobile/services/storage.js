
let _storage = {};
let storage = new Proxy(_storage, {
  get(target, prop) {    return localStorage[prop] ? JSON.parse(localStorage[prop]) : {};  },
  set(target, prop, value) { localStorage[prop] = JSON.stringify(value); return true;  }
});

export default storage;
