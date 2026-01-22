<template>
  <div class="register-container">
    <div class="form-wrapper">
      <h2 class="text-center mb-4">User Registration</h2>

      <!-- 🔵 Global Loader -->
      <div id="global-loader" v-show="loading">
        <div class="droplet"></div>
      </div>

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

        <!-- 🔹 Password with toggle -->
          <div class="password-wrapper">
            <input 
              :type="showPassword.password ? 'text' : 'password'" 
              v-model="form.password" 
              placeholder="Password" 
              :class="{ 'is-invalid': errors.password }" 
            />
            <i 
              v-if="form.password.length > 0"
              class="toggle-password bi"
              :class="showPassword.password ? 'bi-eye-slash' : 'bi-eye'"
              @click="showPassword.password = !showPassword.password"
            ></i>
          </div>

          <!-- 🔹 Confirm Password with toggle -->
          <div class="password-wrapper">
            <input 
              :type="showPassword.confirm ? 'text' : 'password'" 
              v-model="form.password_confirmation" 
              placeholder="Confirm Password" 
            />
            <i 
              v-if="form.password_confirmation.length > 0"
              class="toggle-password bi"
              :class="showPassword.confirm ? 'bi-eye-slash' : 'bi-eye'"
              @click="showPassword.confirm = !showPassword.confirm"
            ></i>
          </div>

        <button type="submit">Register</button>
      </form>

      <p v-if="errors.general" class="error-msg">{{ errors.general[0] }}</p>
      <p v-if="success" class="success-msg">Registration successful!</p>

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
    showPassword: {
      password: false,
      confirm: false
    },
    loading: false,
    success: false,
    errors: {}
  }
},
  methods: {
    togglePassword() {
      this.showPassword[field] = !this.showPassword[field];
    },
    async registerUser() {
      this.loading = true;  // 🔹 show loader
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
            this.errors = data.errors;
          } else if (data.message) {
            this.errors.general = [data.message];
          }
          return;
        }

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

        // 🔹 Redirect after registration
        window.location.href = '/user/dashboard';

      } catch (err) {
        this.errors.general = [err.message];
      } finally {
        this.loading = false; // 🔹 hide loader
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
  background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
              url('/images/Flag_of_Magallanes,_Agusan_del_Norte.webp') no-repeat center center;
  background-size: cover;
}

.form-wrapper {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  width: 100%;
  max-width: 400px;
  position: relative;
}

/* 🔹 Loader styles */
#global-loader {
  display: flex;
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  justify-content: center;
  align-items: center;
  background: rgba(255,255,255,0.8);
  z-index: 999;
}
.droplet {
  width: 40px;
  height: 40px;
  background: #007bff;
  border-radius: 50% 50% 60% 60%;
  animation: drop 0.8s infinite ease-in-out;
}
@keyframes drop {
  0% { transform: translateY(-15px) scale(1); opacity:0.9; }
  50% { transform: translateY(0px) scale(0.85); opacity:1; }
  100% { transform: translateY(15px) scale(1); opacity:0.9; }
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

.password-wrapper {
  position: relative;
}

.password-wrapper input {
  width: 100%;
  padding-right: 2.5rem; /* room for eye icon */
}

.toggle-password {
  position: absolute;
  top: 50%;
  right: 0.75rem;
  transform: translateY(-50%);
  cursor: pointer;
  color: #555;
  font-size: 1.2rem;
}



p a {
  color: #007bff;
  text-decoration: none;
}
p a:hover {
  text-decoration: underline;
}
</style>
