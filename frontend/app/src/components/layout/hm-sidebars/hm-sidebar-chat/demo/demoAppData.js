export default {
  "access": {
    "type": "access_full",
    "steps": [],
  },
  "candidate_id": "6617",
  "comments": {
    "2": {
      "proc_comment_id": "2",
      "proc_process_id": "3",
      "user_id": "1",
      "comment": "tet",
      "attachment": null,
      "proc_step_id": "2",
      "proc_state": "communication",
      "is_hidden": "0",
      "is_manual": "0",
      "created_at": "2020-07-02 00:00:00",
      "scheduled_at": null,
      "user": {
        "id": "1",
        "name": "Тесла",
        "avatar": "/images/content-modules/nophoto.gif"
      },
      "files": []
    },
    "4": {
      "proc_comment_id": "4",
      "proc_process_id": "3",
      "user_id": "1",
      "comment": "test 2",
      "attachment": null,
      "proc_step_id": "2",
      "proc_state": "agreed",
      "is_hidden": "0",
      "is_manual": "0",
      "created_at": "2020-07-03 00:00:00",
      "scheduled_at": null,
      "user": {
        "id": "1",
        "name": "Тесла",
        "avatar": "/images/content-modules/nophoto.gif"
      },
      "files": []
    },
    "5": {
      "proc_comment_id": "5",
      "proc_process_id": "3",
      "user_id": "1",
      "comment": "test3",
      "attachment": null,
      "proc_step_id": "3",
      "proc_state": "agreed",
      "is_hidden": "0",
      "is_manual": "0",
      "created_at": "2020-07-05 00:00:00",
      "scheduled_at": null,
      "user": {
        "id": "1",
        "name": "Тесла",
        "avatar": "/images/content-modules/nophoto.gif"
      },
      "files": []
    },
    "6": {
      "proc_comment_id": "6",
      "proc_process_id": "3",
      "user_id": "1",
      "comment": "test4",
      "attachment": null,
      "proc_step_id": "3",
      "proc_state": "communication",
      "is_hidden": "0",
      "created_at": "2020-07-05 00:00:00",
      "scheduled_at": null,
      "user": {
        "id": "1",
        "name": "Тесла",
        "avatar": "/images/content-modules/nophoto.gif"
      },
      "files": []
    },
    "686": {
      "proc_comment_id": "686",
      "proc_process_id": "139",
      "user_id": "1",
      "comment": "Концерт Шуфутинского",
      "attachment": null,
      "proc_step_id": "2",
      "proc_state": "planned",
      "is_hidden": "1",
      "created_at": "2020-07-25 22:14:37",
      "scheduled_at": "2020-09-03 18:00:00",
      "is_manual": "1",
      "user": {
        "id": "1",
        "name": "Тесла",
        "avatar": "\/images\/content-modules\/nophoto.gif"
      },
      "files": []
    },
  },
  "page_candidate_ids": [
    "6600",
    "6601",
    "6602",
    "6603",
    "6611",
    "6613",
    "6617",
    "6616"
  ],
  "process": {
    "ZEND_DB_ROWNUM": "1",
    "proc_process_id": "3",
    "process_type": "recruitment",
    "item_id": "6617",
    "status": "active",
    "current_step": "1",
    "current_state": "new",
    "editor_roles": null
  },
  "states": {
    "new": "Новый",
    "paused": "Приостановлен",
    "communication": "В процессе коммуникации",
    "planned": "Мероприятие запланировано",
    "done": "Мероприятие проведено",
    "agreed": "Согласован",
    "canceled": "Отклонен"
  },
  "steps": {
    "1": {
      "proc_step_id": "1",
      "process_type": "recruitment",
      "name": "Рекрутер",
      "ordr": "1",
      "editor_roles": "recruiter,recruiter_local",
      "color": null,
      "icon_name": "recruit"
    },
    "2": {
      "proc_step_id": "2",
      "process_type": "recruitment",
      "name": "Руководитель",
      "ordr": "2",
      "editor_roles": "recruiter,recruiter_local,supervisor",
      "color": null,
      "icon_name": "manager"
    },
    "3": {
      "proc_step_id": "3",
      "process_type": "recruitment",
      "name": "ПФО",
      "ordr": "3",
      "editor_roles": "recruiter,recruiter_local",
      "color": null,
      "icon_name": "pfo"
    },
    "4": {
      "proc_step_id": "4",
      "process_type": "recruitment",
      "name": "Медосмотр",
      "ordr": "4",
      "editor_roles": "recruiter,recruiter_local",
      "color": null,
      "icon_name": "medical"
    },
    "5": {
      "proc_step_id": "5",
      "process_type": "recruitment",
      "name": "Подразделение, ответственное за сбор документов",
      "ordr": "5",
      "editor_roles": "recruiter,recruiter_local",
      "color": null,
      "icon_name": "department"
    },
    "6": {
      "proc_step_id": "6",
      "process_type": "recruitment",
      "name": "ДКЗиПК",
      "ordr": "6",
      "editor_roles": "recruiter,recruiter_local",
      "color": null,
      "icon_name": "dkz"
    },
    "7": {
      "proc_step_id": "7",
      "process_type": "recruitment",
      "name": "test",
      "ordr": "9",
      "editor_roles": null,
      "color": null,
      "icon_name": null
    }
  },
  "user": {
    "id": "1",
    "name": "Тесла",
    "avatar": "/images/content-modules/nophoto.gif"
  },
  "urls": {
    "deleteComment": "/recruit/candidate/assign/delete-comment/",
    "saveComment": "/recruit/candidate/assign/save-comment/",
    "saveCommentFile": "/recruit/candidate/assign/save-comment-file/",
    "saveState": "/recruit/candidate/assign/save-state/",
    "toggleComment": "/recruit/candidate/assign/toggle-comment/"
  },
  "status": "0",
  "statuses": [
    {
      "id": 1,
      "title": "Отклик"
    },
    {
      "id": 0,
      "title": "Активный"
    },
    {
      "id": "2_-1",
      "title": "Отклонён"
    },
    {
      "id": "2_-3",
      "title": "Чёрный список"
    },
    {
      "id": "2_-2",
      "title": "Кадровый резерв"
    },
    {
      "id": "2_-4",
      "title": "Самоотказ"
    },
    {
      "id": "2_1",
      "title": "Рекомендован"
    }
  ],
};
