export default {
  //метод записывающий данные по пользователям с учетом поиска и первоначальной загрузки
  SET__AllData (state, dataInServer) {
    state.data = dataInServer
  },
  // метод добавляющий результаты
  SET__AddNewData (state, dataInServer) {
    dataInServer['graduated'].length > 0 ? state.data['graduated'] = state.data['graduated'].concat(dataInServer['graduated']) : ''
    dataInServer['student'].length > 0 ? state.data['student'] = state.data['student'].concat(dataInServer['student']) : ''
    dataInServer['teacher'].length > 0 ? state.data['teacher'] = state.data['teacher'].concat(dataInServer['teacher']) : ''
  },
  //метод добавления , удаления пользователя в ВЫБРАННЫХ
  SET__selectedUser (state, user) {
    if(user.flag) {
      state.dataUsers.push(user.user)
    }
    else if(!user.flag) {
      state.dataUsers.forEach((el,i)=> {
        if(el.id === user.user.id) state.dataUsers.splice(i, 1)
      })
    }
  },
  // метод очистки всех выбранных пользователей
  SET__ClearSelectedUser (state) {
    state.dataUsers = []
  },
  // метод изменения страницы ( след. )
  SET__activePage (state) {
    state.page++
  },
  // метод перехода на первую страницу
  SET__activePageNullified (state) {
    state.page = 1
  },
  // Метод записи поисковой строки и переключение на 1 страницу т.ку. поиск
  SET__search ( state, stringSearch) {
    state.search = stringSearch
    state.page = 1
  },
  // метод обнуления строки
  SET__searchNullified ( state) {
    state.search = ''
  }


}




