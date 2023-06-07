/**
 * @see https://habr.com/ru/post/459050/
 * @see https://github.com/ktsn/vuex-smart-module/blob/master/README.md
 */
import { createMapper, Module } from 'vuex-smart-module';

import actions from './actions';
import getters from './getters';
import mutations from './mutations';
import state from './state';
import { Store } from 'vuex';
import {
  registerModule as registerVuexStoreModule,
  unregisterModule as unregisterVuexStoreModule,
} from 'vuex-smart-module/lib/register';

const HmRecruitersChatModule = new Module({
  actions,
  getters,
  mutations,
  state,
});

export default HmRecruitersChatModule;

export function registerStoreModuleDefault(store) {
  registerVuexStoreModule(
    store,
    // module path. can be string or array of string
    ['HmRecruitersChat'],
    // namespace string which will be when put into the store
    'HmRecruitersChat/',
    // module instance
    HmRecruitersChatModule
  );
}

export function unregisterStoreModuleDefault(store) {
  unregisterVuexStoreModule(store, HmRecruitersChatModule);
}

export const storeModuleMapperDefault = createMapper(HmRecruitersChatModule);
