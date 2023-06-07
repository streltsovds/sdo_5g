import { ActionsFactory } from "../../../utilities";
let retObject = {};

const ModulePrefix = "sidebars/";

const Actions = ["registerSidebar", "changeSidebarState"];

Actions.forEach(actionName => {
  retObject = {
    ...retObject,
    ...ActionsFactory(actionName, ModulePrefix)
  };
});

//export default retObject;
export default {
  registerSidebar(context, payload) {
    const actionName = "registerSidebar";
    context.commit(actionName, payload);
  },
  changeSidebarState(context, payload) {
    const actionName = "changeSidebarState";
    context.commit(actionName, payload);
  },
  closeSidebar(context) {
    context.commit("closeSidebar");
  }
};
