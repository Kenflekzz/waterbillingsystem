import { createRouter, createWebHistory } from 'vue-router';
import AdminLogin from '../components/AdminLogin.vue';
import AdminRegister from '../components/AdminRegister.vue';
import UserLogin from '../components/Userlogin.vue';
import UserRegister from '../components/UserRegister.vue';


const routes = [
  { path: '/admin/login', component: AdminLogin },
  { path: '/admin/register', component: AdminRegister },
  {path: '/user/login', component: UserLogin }, 
  {path: '/user/register', component: UserRegister }, 
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
