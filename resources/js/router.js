import { createRouter, createWebHistory } from 'vue-router';
import Posts from './components/Posts.vue';
import Post from './components/Post.vue';
import Login from './components/Login.vue';
import EditPost from './components/EditPost.vue';
import Register from './components/Register.vue';
import CreatePost from './components/CreatePost.vue';

const routes = [
  {
    path: '/',
    name: 'posts',
    component: Posts,
  },
  {
    path: '/posts/create',
    name: 'create-post',
    component: CreatePost,
  },
  {
    path: '/posts/:slug',
    name: 'post',
    component: Post,
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
  },
  {
    path: '/posts/:slug/edit',
    name: 'edit-post',
    component: EditPost,
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
