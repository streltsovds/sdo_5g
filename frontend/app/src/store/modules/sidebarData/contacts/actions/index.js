import Axios from "axios";

export default {
  // Метод поиска
  SearchUsers(context, data) {
    context.commit('SET__search', data.search) // записал активную строку по поиску
    async function searchInServer(stringSearch) {
      const res = await Axios.get(stringSearch)
      if(res.data) {
        context.commit('SET__AllData',res.data )
      }
    }
    let stringSearch = `/subject/contacts/search/subject_id/${data.id}/${data.search !== '' ? `query/${data.search}/` : ''}`
    searchInServer(stringSearch)
  },
  // метод догрузки пользователей
  uersLoading( context,data) {
    context.commit('SET__activePage')
    console.log(context.getters.allData)
    async function usersLoadingNewxtPage(stringSearch) {
      const res = await Axios.get(stringSearch)
      if(res && res.data) {
        console.log(context.getters['allData'])
        context.commit('SET__AddNewData', res.data)
        console.log(context.getters['allData'])
      }
    }
    let titleSearch = context.getters['searchString'];
    let activePage = context.getters['activePage'];
    let usersLoadingSearchNext = `/subject/contacts/search/subject_id/${data.subject_id}/${titleSearch !== '' ? `query/${titleSearch}/` : ''}page/${activePage}`
    usersLoadingNewxtPage(usersLoadingSearchNext)
  //  graduated
  //  student
  //  teacher
  }
}
