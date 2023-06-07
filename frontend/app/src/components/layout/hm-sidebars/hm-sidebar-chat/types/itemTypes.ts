enum ItemTypes {
  TYPE_VACANCY = 'vacancy',
  TYPE_RECRUITER = 'recruiter',
  TYPE_CANDIDATE = 'candidate',
  TYPE_CANDIDATE_EMAIL = 'candidate_email',
  TYPE_CANDIDATE_PHONE = 'candidate_phone',
  TYPE_PROFILE = 'profile',
}

enum ItemTypeByAt {
  '#' = ItemTypes.TYPE_VACANCY,
  '@' = ItemTypes.TYPE_RECRUITER,
  '!' = ItemTypes.TYPE_CANDIDATE,
  '$' = ItemTypes.TYPE_PROFILE,
}

export { ItemTypes, ItemTypeByAt };
