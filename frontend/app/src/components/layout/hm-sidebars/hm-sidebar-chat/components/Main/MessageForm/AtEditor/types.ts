// import Candidate from '~/types/candidate';
// import Profile from '~/types/profile';
// import Recruiter from '~/types/recruiter';
// import Vacancy from '~/types/vacancy';

// interface AtWhoType {
//   list: Array<Candidate | Recruiter | Vacancy>;
//   cur: number;
//   range: Range;
//   offset: number;
// }

// type PRVN = Candidate | Recruiter | Vacancy | null;

// type AtTypes = '!' | '@' | '#' | '$';

// type altAtsType = '"' | '№' | ';';

enum altAts {
  '"' = '@',
  '№' = '#',
  ';' = '$',
}
// interface AtDataType {
//   bindsValue: boolean;
//   lastIsInsertItem: boolean;
//   members: {
//     '#': Array<Candidate>;
//     '@': Array<Recruiter>;
//     '!': Array<Vacancy>;
//     '$': Array<Profile>;
//   };
//   atwho: AtWhoType | null;
//   isTyping: boolean;
//   typingTimeoutId: number | null;
//   lastReq: Function;
//   curAt: string | null;
//   withoutRegExpAction: boolean;
// }

// interface NewRangeData {
//   endOffset: number;
//   endContainer: Node;
//   startOffset: number;
//   startContainer: Node;
//   oldChildNodes: NodeListOf<ChildNode>;
//   newChildNodes: NodeListOf<ChildNode>;
// }

export {
  // AtDataType,
  // PRVN,
  // AtTypes,
  // AtWhoType,
  // NewRangeData,
  // altAtsType,
  altAts,
};
