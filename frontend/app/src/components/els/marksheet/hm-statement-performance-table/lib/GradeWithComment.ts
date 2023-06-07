class GradeWithComment {
  value: any;
  dialog: boolean = false;
  comments: string = "";

  constructor(opts: Partial<GradeWithComment>) {
    // noinspection TypeScriptValidateTypes
    Object.assign(this, opts);
  }
}

export default GradeWithComment;
