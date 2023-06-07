import Message from '../types/message';
import User from '../types/user';

const wsInitConfig = {
  port: '8080',
  host: '127.0.0.1',
  sessionId: '',
  roomId: 'room',
  namespace: ''
}

class HmRecruitersChatState {
  messages = [];
  // searchString: string = '';
  wsConfig = wsInitConfig;
  User = {};
  api = {
    subject_id: "",
    lesson_id: ""
  };
  editingMessageId = null;
  errors = { fatal: null };
}

export default HmRecruitersChatState;
