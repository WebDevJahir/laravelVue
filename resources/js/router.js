import Vue from 'vue';
import VueRouter from 'vue-router';

import login from './components/LoginComponent.vue';
import admin from './components/AdminComponent.vue';
import role from './components/RolesComponent.vue';

Vue.use(VueRouter)

const routes = [
{
	path: '/',
	component:login
},
{
	path: '/login',
	component: login
},
{
	path: '/admin',
	component: admin,
	name:'Admin',
	beforeEnter:(to,from,next)=>{
		axios.get('/api/verify')
		.then(res => next())
		.catch(err => next('/login'))
	},
	children:[{
		path:'role',
		component: role
	}]
}
]

const router = new VueRouter({routes})
router.beforeEach((to,from,next)=>{
	const token = localStorage.getItem('token') || null 
	window.axios.defaults.headers['Authorization'] = "Bearer " + token;
	next();
}) 

export default router