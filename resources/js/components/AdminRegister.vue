<template>
  <div class="register-container">
    <div class="form-wrapper">
      <h2 class="text-center mb-4">Create Admin Account</h2>
      <form @submit.prevent="register">
        <div class="row">
          <div class="col-md-6 mb-3">
            <input
              v-model="first_name"
              class="form-control"
              type="text"
              placeholder="First Name"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <input
              v-model="last_name"
              class="form-control"
              type="text"
              placeholder="Last Name"
              required
            />
          </div>
        </div>

        <input
          v-model="email"
          class="form-control mb-3"
          type="email"
          placeholder="Email Address"
          required
        />

        <div class="row">
          <div class="col-md-6 mb-3">
            <input
              v-model="password"
              class="form-control"
              type="password"
              placeholder="Password"
              required
            />
          </div>
          <div class="col-md-6 mb-3">
            <input
              v-model="password_confirmation"
              class="form-control"
              type="password"
              placeholder="Confirm Password"
              required
            />
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Create Account</button>

        <p v-if="error" class="error-msg mt-3">{{ error }}</p>

        <p class="mt-3 text-center">
          Already have an account?
          <router-link to="/admin/login">Login here</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      first_name: '',
      last_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      error: ''
    };
  },
  methods: {
    async register() {
      this.error = '';
      try {
        const response = await fetch('/admin/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN':
              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            first_name: this.first_name,
            last_name: this.last_name,
            email: this.email,
            password: this.password,
            password_confirmation: this.password_confirmation
          })
        });

        const data = await response.json();

        if (!response.ok) {
          this.error = data.errors
            ? Object.values(data.errors).flat().join(' ')
            : data.message || 'Registration failed.';
          return;
        }

        window.location.href = '/admin/dashboard';
      } catch (err) {
        this.error = 'Registration failed: ' + err.message;
      }
    }
  }
};
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),
    url('/images/Flag_of_Magallanes,_Agusan_del_Norte.webp') no-repeat center center;
  background-size: cover;
}

.form-wrapper {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 500px;
}

input.form-control {
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 1rem;
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

.error-msg {
  color: red;
  font-size: 0.9rem;
}

p a {
  color: #007bff;
  text-decoration: none;
}

p a:hover {
  text-decoration: underline;
}
</style>
