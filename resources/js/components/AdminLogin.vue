<template>
  <div>
    <!-- ✅ Header Component -->
    <Header :homepage="headerData" :showSignIn="false" />

    <!-- ✅ Admin Login Form -->
    <div class="login-wrapper vh-100 d-flex align-items-center justify-content-center">
      <div class="card shadow-sm p-4 w-100" style="max-width: 400px;">
        <h2 class="text-center mb-4">Admin Login</h2>

        <form @submit.prevent="login">
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input v-model="email" type="email" class="form-control" id="email" />
          </div>

          <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="password"
              class="form-control"
              id="password"
            />
            <i
              v-if="password.length > 0"
              :class="['toggle-password', showPassword ? 'bi bi-eye-slash' : 'bi bi-eye']"
              @click="showPassword = !showPassword"
            ></i>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <router-link to="/admin/reset-password" class="small text-decoration-none">
              Forgot password?
            </router-link>
          </div>

          <button type="submit" class="btn btn-primary w-100">Login</button>

          <!-- Error Message -->
          <div v-if="error" class="alert alert-danger mt-3" role="alert">
            {{ error }}
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import Header from './Header.vue';

// ⚠️ No WaterDropletLoader import needed anymore

export default {
  name: "AdminLogin",
  components: { Header },
  data() {
    return {
      email: '',
      password: '',
      error: '',
      showPassword: false,
      headerData: {
        header_bg: '#741aac',
        logo: '/images/MAGALLANES_LOGO.png',
        header_title: 'Magallanes Water Billing System',
        nav_home: 'Home',
        nav_bills: 'Bills',
        nav_contact: 'Contact Us',
        sign_in_text: 'Sign In'
      }
    };
  },
  methods: {
  async login() {
    this.error = '';

    // Frontend pre-validation
    if (!this.email || !this.password) {
      this.error = 'Please fill in both email and password.';
      return;
    }

    if (typeof showLoader === "function") showLoader();

        try {
          const response = await fetch('/admin/login', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: this.email, password: this.password }),
            credentials: 'same-origin'
          });

          let data;
          const contentType = response.headers.get('content-type');

          if (contentType && contentType.includes('application/json')) {
            data = await response.json();
          } else {
            data = { success: false, message: 'Unexpected server response. Please try again.' };
          }

          if (!response.ok || !data.success) {
            this.error = data.message || 'Login failed. Please check your credentials.';
            if (typeof hideLoader === "function") hideLoader();
            return;
          }

          window.location.assign(data.redirect || '/admin/dashboard');

        } catch (err) {
          this.error = 'Login failed. Please try again.';
          if (typeof hideLoader === "function") hideLoader();
        }
      }

  }
};
</script>

<style scoped>
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
              url('/images/1000019384.jpg') no-repeat center center;
  background-size: cover;
}

.card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

/* 👁️ Eye Icon Style */
.toggle-password {
  position: absolute;
  right: 12px;
  top: 73%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 1.2rem;
  color: #555;
}

button {
  padding: 0.75rem;
  border: none;
  background: #007bff;
  color: white;
  font-size: 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

button:hover {
  background: #0056b3;
}
</style>
