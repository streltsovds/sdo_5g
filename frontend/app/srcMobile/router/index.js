import Vue from 'vue'
import Router from 'vue-router'
import AuthPage from '../components/AuthPage'
import HomePage from '../components/HomePage'
import IdeaPage from '../components/IdeaPage'
import CoursesPage from '../components/CoursesPage'
import KbasePage from '../components/KbasePage'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'HomePage',
      component: HomePage,
      meta: {title: 'Главная'}
    },
    {
      path: '/auth-page',
      name: 'AuthPage',
      component: AuthPage,
      meta: {title: 'Авторизация'}
    },
    {
      path: '/courses-page',
      name: 'CoursesPage',
      component: CoursesPage,
      meta: {title: 'Мои курсы'}
    },
    {
      path: '/kbase-page',
      name: 'KbasePage',
      component: KbasePage,
      meta: {title: 'База знаний'}
    },
    {
      path: '/idea-page',
      name: 'IdeaPage',
      component: IdeaPage,
      meta: {title: 'Идеи'}
    }
  ]
})
