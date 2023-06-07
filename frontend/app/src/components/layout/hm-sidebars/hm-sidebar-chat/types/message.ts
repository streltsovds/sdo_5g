import User from './recruiter';

export default interface Message {
  created_at: string;
  message: string;
  message_id: number | null;
  user_id: number;
  user: User;
}
