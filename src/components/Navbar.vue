<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <router-link class="navbar-brand fw-bold" to="/">Shabu House</router-link>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="เปิดเมนูนำทาง"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="navbarNav" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>

          <li class="nav-item dropdown">
            <button
              class="nav-link dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              Product
            </button>
            <ul class="dropdown-menu">
              <li><router-link class="dropdown-item" to="/product">Product</router-link></li>
              <li><router-link class="dropdown-item" to="/show_product">Show Product</router-link></li>
              <li v-if="isLoggedIn"><router-link class="dropdown-item" to="/product-edit">แก้ไข Product</router-link></li>
            </ul>
          </li>

          <li v-if="!isLoggedIn" class="nav-item">
            <router-link class="nav-link" to="/customer">Customer</router-link>
          </li>
          <li v-else class="nav-item">
            <router-link class="nav-link" to="/customer_crud">Customer CRUD</router-link>
          </li>

          <li v-if="!isLoggedIn" class="nav-item">
            <router-link class="nav-link" to="/employee">Employee</router-link>
          </li>
          <li v-else class="nav-item">
            <router-link class="nav-link" to="/employee_crud">Employee CRUD</router-link>
          </li>

          <li v-if="isLoggedIn" class="nav-item">
            <router-link class="nav-link" to="/asset">Asset</router-link>
          </li>

          <li class="nav-item">
            <router-link class="nav-link" to="/about">About</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/contact">Contact</router-link>
          </li>

          <li v-if="!isLoggedIn" class="nav-item dropdown">
            <button
              class="nav-link dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              Register
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><router-link class="dropdown-item" to="/add_customer">Register</router-link></li>
              <li><router-link class="dropdown-item" to="/login">Login</router-link></li>
            </ul>
          </li>
        </ul>

        <div v-if="isLoggedIn" class="d-flex align-items-center ms-lg-3 mt-2 mt-lg-0">
          <span class="text-white me-3">
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            {{ userName }}
          </span>
          <button type="button" class="btn btn-danger btn-sm" @click="logout">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: "Navbar",
  data() {
    return {
      isLoggedIn: false,
      userName: ""
    };
  },
  mounted() {
    this.checkLogin();
    window.addEventListener("storage", this.checkLogin);
  },
  beforeUnmount() {
    window.removeEventListener("storage", this.checkLogin);
  },
  methods: {
    checkLogin() {
      this.isLoggedIn = localStorage.getItem("adminLogin") === "true";

      if (!this.isLoggedIn) {
        this.userName = "";
        return;
      }

      try {
        const user = JSON.parse(localStorage.getItem("user") || "null");
        this.userName = user && (user.name || user.username) ? user.name || user.username : "Admin";
      } catch (error) {
        this.userName = "Admin";
      }
    },
    async logout() {
      localStorage.removeItem("adminLogin");
      localStorage.removeItem("user");
      this.checkLogin();
      await this.$router.push("/login");
    }
  },
  watch: {
    $route() {
      this.checkLogin();
    }
  }
};
</script>
