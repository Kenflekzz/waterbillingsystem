<template>
  <div>
    <!-- ✅ Header Component -->
    <Header
      :homepage="{
        header_bg: '#741aac',
        logo: '/images/MAGALLANES_LOGO.png',
        header_title: 'Magallanes Water Billing System',
        nav_home: 'Home',
        nav_bills: 'Bills',
        nav_contact: 'Contact Us',
        sign_in_text: 'Sign In'
      }"
      :showSignIn="false"
    />

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

          <!-- Password -->
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input v-model="password" type="password" class="form-control" id="password" />
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

export default {
  name: "AdminLogin",
  components: { Header },
  data() {
    return {
      email: '',
      password: '',
      error: ''
    };
  },
  methods: {
    async login() {
      this.error = '';

      try {
        const response = await fetch('/api/admin/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN':
              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password
          }),
          credentials: 'include'
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          this.error = data.message || 'Invalid credentials, please contact the system admin.';
          return;
        }

        // Redirect to admin dashboard
        setTimeout(() => {
          window.location.assign(data.redirect || '/admin/dashboard');
        }, 50);
      } catch (err) {
        this.error = 'Login failed: ' + err.message;
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
  background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),
    url('/images/Flag_of_Magallanes,_Agusan_del_Norte.webp') no-repeat center center;
  background-size: cover;
}

.card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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
