import Home from '../Home.vue'
import Comments from '../Comments.vue'
import Reports from '../Reports.vue'
import Chain from '../Chain.vue'
import Users from '../Users.vue'
import ProfileEdit from '../ProfileEdit.vue'
import Profile from '../Profile.vue'
import BanIp from '../BanIp.vue'
import Spam from '../Spam.vue'
import Setting from '../Setting.vue'
import SettingAntiFlood from '../SettingAntiFlood.vue'
import SettingAuthorize from '../SettingAuthorize.vue'
import SettingStopWords from '../SettingStopWords.vue'
import SettingPermissions from '../SettingPermissions.vue'
import SettingRoles from '../SettingRoles.vue'
import SettingLanguages from '../SettingLanguages.vue'
import SettingSMTP from '../SettingSMTP.vue'
import Login from '../Login.vue'
import Page404 from '../404.vue'
import Pages from '../Pages.vue'
import PageEdit from '../PageEdit.vue'

const routes = [
  { path: '/', component: Home, children: [
    { path: '', component: Comments },
    { path: '/pages', component: Pages },
    { path: '/pages/:id/edit', component: PageEdit },
    { path: '/reports/:id', component: Reports },
    { path: '/chain/:mid', component: Chain },
    { path: '/profiles', component: Users },
    { path: '/profile/:id/edit', component: ProfileEdit },
    { path: '/profile/:id', component: Profile },
    { path: '/banip', component: BanIp },
    { path: '/setting', component: Setting },
    { path: '/setting/antiflood', component: SettingAntiFlood },
    { path: '/setting/authorize', component: SettingAuthorize },
    { path: '/setting/stopwords', component: SettingStopWords },
    { path: '/setting/spam', component: Spam },
    { path: '/setting/permissions', component: SettingPermissions },
    { path: '/setting/roles', component: SettingRoles },
    { path: '/setting/languages', component: SettingLanguages },
    { path: '/setting/smtp', component: SettingSMTP },
  ]},
  { path: '/login', component: Login, name: 'login' },
  { path: '*', component: Page404 },
  // { path: '/comments', component: Comments },
  // { path: '/about', component: About },
  // { path: '/catalog/page', component: Page },
];

export default routes;