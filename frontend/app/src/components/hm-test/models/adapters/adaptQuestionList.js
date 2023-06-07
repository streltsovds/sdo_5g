const getClusterName = (clusterList, id) =>
  clusterList[id] ? clusterList[id].name : null;

const getQuestion = (questionList, id) =>
  questionList[id] ? questionList[id] : null;

export default (progress, { clusters, index, questions, attempt, indicators, criteria }) => {
  let temp = [];
  for (const progressItem of progress) {
    if (attempt.method === 'competence') {
      const element = {
        id: progressItem.itemId,
        criteria: Object.entries(index[progressItem.itemId]).map(([key, value]) => {
          const indicatorsItem = {};
          value.forEach(item => {
            indicatorsItem[item] = indicators[item]
            indicatorsItem[item]['question_id'] = indicators[item]['indicator_id']
          });
          const criteriaItem = criteria[key];
          criteriaItem.indicators = indicatorsItem;
          criteriaItem.question_id = criteriaItem.criterion_id;
          return criteriaItem;
        }),
        // questions: index[progressItem.itemId].map(question_id =>
        //   getQuestion(questions, question_id)
        // ),
        name: getClusterName(clusters, progressItem.itemId)
      };
      temp.push(element);
    } else {
      const element = {
        id: progressItem.itemId,
        questions: index[progressItem.itemId].map(question_id =>
          getQuestion(questions, question_id)
        ),
        name: getClusterName(clusters, progressItem.itemId)
      };
      temp.push(element);
    }
  }
  return temp;
};
