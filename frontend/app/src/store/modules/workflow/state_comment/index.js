import workflow from "../workflow";

let state_comment = {
  isSubmit: false
};

export default {
  ...workflow,
  state: state_comment
};
