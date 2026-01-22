<template>
  <div>
    <!-- ✅ Header Component -->
    <Header
       :homepage="headerData"
       :showSignIn="false"
       :hideContactUs="true"
    />

    <!-- ✅ Login Form -->
    <div class="login-wrapper vh-100 d-flex align-items-center justify-content-center">
      <div class="card shadow-sm p-4 w-100" style="max-width: 400px;">
        <h2 class="text-center mb-4">User Login</h2>

        <form @submit.prevent="login">
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input v-model="email" type="email" class="form-control" id="email" />
          </div>

          <!-- Password -->
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
              :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"
              class="toggle-password"
              @click="showPassword = !showPassword"
            ></i>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <router-link to="/user/reset-password" class="small text-decoration-none">
              Forgot password?
            </router-link>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            Login
          </button>

          <!-- Error Message -->
          <div v-if="error" class="alert alert-danger mt-3" role="alert">
            {{ error }}
          </div>

          <!-- Register Link -->
          <div class="text-center mt-3">
            <p class="mb-0">
              Don’t have an account?
              <router-link to="/user/register" class="text-primary fw-bold">
                Register here
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import Header from './Header.vue';

export default {
  name: "UserLogin",
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
        nav_contact: 'Contact Us',
        sign_in_text: 'Sign In'
      }
    };
  },

  methods: {
    async login() {
  this.error = '';

  // ✅ Frontend pre-validation
  if (!this.email || !this.password) {
    this.error = 'Please fill in both email and password.';
    return;
  }

  if (typeof showLoader === "function") showLoader(); // Show loader

  try {
    const response = await fetch('/user/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        email: this.email,
        password: this.password
      }),
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
      this.error = data.message || 'Invalid credentials, please contact the admin.';
      if (typeof hideLoader === "function") hideLoader();
      return;
    }

    window.location.assign(data.redirect || '/user/home');

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
              url('/images/Flag_of_Magallanes,_Agusan_del_Norte.webp') no-repeat center center;
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
