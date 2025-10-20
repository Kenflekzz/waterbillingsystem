<template>
  <div class="register-container">
    <div class="form-wrapper">
      <h2 class="text-center mb-4">User Registration</h2>
      <form @submit.prevent="registerUser">
        
        <input v-model="form.first_name" 
               placeholder="First Name" 
               :class="{ 'is-invalid': errors.first_name }" />
        <p v-if="errors.first_name" class="error-msg">{{ errors.first_name[0] }}</p>

        <input v-model="form.last_name" 
               placeholder="Last Name" 
               :class="{ 'is-invalid': errors.last_name }" />
        <p v-if="errors.last_name" class="error-msg">{{ errors.last_name[0] }}</p>

        <input v-model="form.meter_number" 
               placeholder="Meter Number" 
               :class="{ 'is-invalid': errors.meter_number }" />
        <p v-if="errors.meter_number" class="error-msg">{{ errors.meter_number[0] }}</p>

        <input v-model="form.phone_number" 
               placeholder="Phone Number" 
               :class="{ 'is-invalid': errors.phone_number }" />
        <p v-if="errors.phone_number" class="error-msg">{{ errors.phone_number[0] }}</p>

        <input v-model="form.email" type="email" 
               placeholder="Email" 
               :class="{ 'is-invalid': errors.email }" />
        <p v-if="errors.email" class="error-msg">{{ errors.email[0] }}</p>

        <input v-model="form.password" type="password" 
               placeholder="Password" 
               :class="{ 'is-invalid': errors.password }" />
        <p v-if="errors.password" class="error-msg">{{ errors.password[0] }}</p>

        <input v-model="form.password_confirmation" 
               type="password" 
               placeholder="Confirm Password" />

        <button type="submit">Register</button>
      </form>

      <p v-if="errors.general" class="error-msg">{{ errors.general[0] }}</p>
      <p v-if="success" class="success-msg">Registration successful!</p>

      <!-- 🔹 Link to Login page -->
      <p class="mt-3 text-center">
        Already have an account?
        <router-link to="/user/login">Login here</router-link>
      </p>
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
      errors: {} // hold field-specific validation errors
    }
  },
  methods: {
    async registerUser() {
      this.success = false;
      this.errors = {};

      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      try {
        const response = await fetch('/user/register', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.form)
        });

        let data;
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
          data = await response.json();
        } else {
          const text = await response.text();
          throw new Error(`Server returned HTML instead of JSON:\n${text.substring(0, 200)}...`);
        }

        if (!response.ok) {
          if (data.errors) {
            this.errors = data.errors; // Laravel returns { field: [messages] }
          } else if (data.message) {
            this.errors.general = [data.message];
          }
          return;
        }

        // ✅ Registration success
        this.success = true;
        this.errors = {};
        this.form = {
          first_name: '',
          last_name: '',
          meter_number: '',
          phone_number: '',
          email: '',
          password: '',
          password_confirmation: ''
        };

        window.location.href = '/user/dashboard';

      } catch (err) {
        this.errors.general = [err.message];
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

input.is-invalid {
  border-color: red;
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
  margin-top: -0.5rem;
  font-size: 0.9rem;
}

.success-msg {
  color: green;
  margin-top: 0.5rem;
}

/* 🔹 Styling for login link */
p a {
  color: #007bff;
  text-decoration: none;
}
p a:hover {
  text-decoration: underline;
}
</style>
