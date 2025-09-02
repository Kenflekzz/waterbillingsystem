<template>
  <div class="login-wrapper vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm p-4 w-100" style="max-width: 400px;">
      <h2 class="text-center mb-4">User Login</h2>
      <form @submit.prevent="login">
        <div class="mb-3">
          <label for="meter_number" class="form-label">Meter Number</label>
          <input
            v-model="meter_number"
            type="text"
            class="form-control"
            id="meter_number"
            required
          />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input
            v-model="password"
            type="password"
            class="form-control"
            id="password"
            required
          />
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>

        <div v-if="error" class="alert alert-danger mt-3" role="alert">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      meter_number: '',
      password: '',
      error: ''
    };
  },
  methods: {
    async login() {
      this.error = '';
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
            meter_number: this.meter_number,
            password: this.password
          }),
          credentials: 'include' // include session cookies
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          this.error = data.message || 'Invalid credentials, please contact the admin.';
          return;
        }

        // ✅ Force full page reload to Blade dashboard
        window.location.href = data.redirect;

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
  min-height: 100vh;
  background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
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
