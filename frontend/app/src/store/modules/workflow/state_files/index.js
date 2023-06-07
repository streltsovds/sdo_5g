import workflow from "../workflow";

let state_files = {
  isSubmit: false
};

export default {
  ...workflow,
  state: state_files
};
