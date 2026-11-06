import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/show_product.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/about',
    name: 'about',
    component: () => import('../views/AboutView.vue')
  },
  {
    path: '/product',
    alias: '/show_product',
    name: 'product',
    component: () => import('../views/show_product.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/assets',
    alias: '/asset',
    name: 'assets',
    component: () => import('../views/product_edit.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/product-edit',
    name: 'product-edit',
    component: () => import('../views/ProductManage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/customer',
    name: 'customers',
    component: () => import('../views/Customer.vue')
  },
  {
    path: '/customer_crud',
    name: 'customer-crud',
    component: () => import('../views/Customer_crud.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/employee',
    name: 'employee',
    component: () => import('../views/Employee.vue')
  },
  {
    path: '/employee_crud',
    name: 'employee-crud',
    component: () => import('../views/Employee.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/add-customer',
    alias: '/add_customer',
    name: 'add-customer',
    component: () => import('../views/Add_customer.vue')
  },
  {
    path: '/add-employee',
    name: 'add-employee',
    component: () => import('../views/Add_employee.vue')
  },
  {
    path: '/edit-customer/:id',
    name: 'edit-customer',
    component: () => import('../views/Edit_customer.vue')
  },
  {
    path: '/edit-employee/:id',
    name: 'edit-employee',
    component: () => import('../views/Edit_employee.vue')
  },
  {
    path: '/edit-contact/:id',
    name: 'edit-contact',
    component: () => import('../views/Edit_contact.vue')
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import('../views/contacts.vue')
  },
  {
    path: '/add-contact',
    name: 'add-contact',
    component: () => import('../views/Add_contact.vue')
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Login.vue')
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem('adminLogin') === 'true'

  if (to.meta.requiresAuth && !isLoggedIn) {
    next('/login')
  } else if (to.path === '/login' && isLoggedIn) {
    next('/')
  } else {
    next()
  }
})

export default router
