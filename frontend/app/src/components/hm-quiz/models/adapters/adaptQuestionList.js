const getClusterName = (clusterList, id) =>
  clusterList[id] ? clusterList[id].name : null;

const getQuestion = (questionList, id) =>
  questionList[id] ? questionList[id] : null;

export default (progress, { clusters, index, questions }) => {
  let temp = [];
  for (const progressItem of progress) {
    const element = {
      id: progressItem.itemId,
      questions: index[progressItem.itemId].map(question_id =>
        getQuestion(questions, question_id)
      ),
      name: getClusterName(clusters, progressItem.itemId)
    };
    temp.push(element);
  }
  return temp;
};
