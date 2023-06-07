export default data => {
  let retObj = {};
  if (data) {
    retObj = {
      exerciseName: {
        value: data.titleLesson
      },
      courseName: {
        value: data.titleCourse
      },
      questionsCount: {
        value: data.questionsCount
      }
    };
    if (data.attempts && data.attempts !== ``) {
      retObj[`attemptsCount`] = {
        key: `попыток`,
        value: {
          left: {
            value: data.attempts.split(`/`)[0]
              ? data.attempts.split(`/`)[0].trim()
              : `неизвестно`
          },
          total: {
            value: data.attempts.split(`/`)[1]
              ? data.attempts.split(`/`)[1].trim()
              : `неизвестно`
          }
        }
      };
    }
    return retObj;
  } else {
    return {};
  }
};
