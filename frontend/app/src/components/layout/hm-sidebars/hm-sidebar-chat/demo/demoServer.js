const _ = require('lodash');
const multiparty = require('multiparty');

/** задержка ответа на запрос для целей тестирования */
const QUERY_DELAY = 1000;

// prettier-ignore
function jsonQueryDelay(res, data) {
  _.delay(
    function() {
      res.json(data);
    },
    QUERY_DELAY
  );
}

// function requireParam(req, res, paramName) {
//   let paramValue = req.params[paramName];
//   if (_.isNil(paramValue) || paramValue === '') {
//     res.status(400).send('argument ' + paramName + ' is missing');
//     throw Error('argument ' + paramName + ' is missing');
//   }
//   return paramValue;
// }

function requireField(formFields, res, paramName) {
  let paramValue = formFields ? formFields[paramName] : undefined;
  if (_.isNil(paramValue) || paramValue === '') {
    res.status(400).send('argument ' + paramName + ' is missing');
    throw Error('argument ' + paramName + ' is missing');
  }
  if (_.isArray(paramValue) && paramValue.length === 1) {
    paramValue = paramValue[0];
  }
  return paramValue;
}

function requireIntField(formFields, res, paramName) {
  let field = requireField(formFields, res, paramName);
  return parseInt(field, 10);
}

function randomId() {
  return _.random(1000, 1000000);
}

/**
 * Обработка запросов демо-приложения для webpack
 * @see https://expressjs.com/en/guide/routing.html
 **/

// prettier-ignore
const demoServer = function(expressApp, server, compiler) {
  expressApp.post(
    '/recruit/candidate/assign/save-comment/',
    function(req, res) {
      new multiparty.Form().parse(req, function(err, fields, _files) {
        // console.log('fields', fields);
        let commentId;

        try {
          commentId = requireIntField(fields, res, 'comment_id');
        } catch (e) {
          console.error(e);
          return;
        }

        commentId = commentId || randomId();

        jsonQueryDelay(res, { comment_id: commentId });
      });
    }
  );

  expressApp.post(
    '/recruit/candidate/assign/delete-comment/',
    function(req, res) {
      new multiparty.Form().parse(req, function(err, fields, _files) {
        // console.log('fields', fields);
        let commentId;

        try {
          commentId = requireIntField(fields, res, 'comment_id');
        } catch (e) {
          console.error(e);
          return;
        }

        jsonQueryDelay(
          res,
          {
            comment_id: commentId,
            success: true,
          }
        );
      });
    }
  );

  expressApp.post(
    '/recruit/candidate/assign/toggle-comment/',
    function(req, res) {
      new multiparty.Form().parse(req, function(err, fields, _files) {
        // console.log('fields', fields);
        let commentId, show;

        try {
          commentId = requireIntField(fields, res, 'comment_id');
          show = requireField(fields, res, 'show');
        } catch (e) {
          console.error(e);
          return;
        }

        jsonQueryDelay(
          res,
          {
            comment_id: commentId,
            success: true,
          }
        );
      });
    }
  );

  expressApp.post(
    '/recruit/candidate/assign/save-state/',
    function(req, res) {
      new multiparty.Form().parse(req, function(err, fields, _files) {
        // console.log('fields', fields);
        let processId, stepId, state;

        try {
          processId = requireIntField(fields, res, 'process_id');
          stepId = requireIntField(fields, res, 'step_id');
          state = requireField(fields, res, 'state');
        } catch (e) {
          console.error(e);
          return;
        }

        jsonQueryDelay(
          res,
          {
            id: randomId()
          }
        );
      });
    }
  );
};

module.exports = demoServer;
