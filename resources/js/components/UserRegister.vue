<template>
  <div class="register-container">
    <div class="form-wrapper">
      <h2 class="text-center mb-4">User Registration</h2>
      <form @submit.prevent="registerUser">
        <input v-model="form.first_name" placeholder="First Name" required />
        <input v-model="form.last_name" placeholder="Last Name" required />
        <input v-model="form.meter_number" placeholder="Meter Number" required />
        <input v-model="form.phone_number" placeholder="Phone Number" required />
        <input v-model="form.email" type="email" placeholder="Email" required />
        <input v-model="form.password" type="password" placeholder="Password" required />
        <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" required />

        <button type="submit">Register</button>
      </form>

      <p v-if="error" class="error-msg">{{ error }}</p>
      <p v-if="success" class="success-msg">Registration successful!</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        meter_number: '',
        phone_number: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      success: false,
      error: null
    }
  },
  methods: {
    async registerUser() {
      this.success = false;
      this.error = null;
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      try {
        const response = await fetch('/api/user/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
           },
          body: JSON.stringify(this.form)
        });

        if (!response.ok) {
          const errData = await response.json();
          this.error = errData.message || 'Registration failed';
          return;
        }

        window.location.href  = '/user/dashboard';
        this.success = true;
        this.form = {
          first_name: '',
          last_name: '',
          meter_number: '',
          phone_number: '',
          email: '',
          password: '',
          password_confirmation: ''
        };
      } catch (err) {
        this.error = 'Something went wrong.';
      }
    }
  }
}
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
  max-width: 400px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

input {
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 8px;
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
  margin-top: 0.5rem;
}

.success-msg {
  color: green;
  margin-top: 0.5rem;
}
</style>
