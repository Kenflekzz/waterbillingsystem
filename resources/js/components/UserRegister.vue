<template>
  <div class="register-container">
    <div class="form-wrapper">
      <h2 class="text-center mb-4">User Registration</h2>

      <!-- 🔵 Global Loader -->
      <div id="global-loader" v-show="loading">
        <div class="droplet"></div>
      </div>

      <form @submit.prevent="registerUser">
        <!-- 🔹 Meter Number with lookup button -->
        <div class="meter-number-wrapper">
          <input 
            v-model="form.meter_number" 
            placeholder="Meter Number *" 
            :class="{ 'is-invalid': errors.meter_number }"
            @blur="lookupMeterNumber"
            @input="onMeterNumberChange"
          />
          <button 
            type="button" 
            @click="lookupMeterNumber" 
            class="lookup-btn"
            :disabled="!form.meter_number || meterLookupLoading"
          >
            <span v-if="meterLookupLoading">⏳</span>
            <span v-else>🔍</span>
          </button>
        </div>
        <p v-if="errors.meter_number" class="error-msg">{{ errors.meter_number[0] }}</p>
        <p v-if="meterLookupSuccess" class="success-msg meter-success">✓ Meter verified! Details auto-filled below</p>

        <input 
          v-model="form.first_name" 
          placeholder="First Name *" 
          :class="{ 'is-invalid': errors.first_name, 'auto-filled': autoFilledFields.first_name }" 
          :readonly="autoFilledFields.first_name"
        />
        <p v-if="errors.first_name" class="error-msg">{{ errors.first_name[0] }}</p>

        <input 
          v-model="form.last_name" 
          placeholder="Last Name *" 
          :class="{ 'is-invalid': errors.last_name, 'auto-filled': autoFilledFields.last_name }" 
          :readonly="autoFilledFields.last_name"
        />
        <p v-if="errors.last_name" class="error-msg">{{ errors.last_name[0] }}</p>

        <input 
          v-model="form.phone_number" 
          placeholder="Phone Number *" 
          :class="{ 'is-invalid': errors.phone_number, 'auto-filled': autoFilledFields.phone_number }" 
          :readonly="autoFilledFields.phone_number"
        />
        <p v-if="errors.phone_number" class="error-msg">{{ errors.phone_number[0] }}</p>

        <input 
          v-model="form.email" 
          type="email" 
          placeholder="Email *" 
          :class="{ 'is-invalid': errors.email, 'auto-filled': autoFilledFields.email }" 
        />
        <p v-if="errors.email" class="error-msg">{{ errors.email[0] }}</p>

        <!-- 🔹 Password with toggle -->
        <div class="password-wrapper">
          <input 
            :type="showPassword.password ? 'text' : 'password'" 
            v-model="form.password" 
            placeholder="Password *" 
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
            placeholder="Confirm Password *" 
          />
          <i 
            v-if="form.password_confirmation.length > 0"
            class="toggle-password bi"
            :class="showPassword.confirm ? 'bi-eye-slash' : 'bi-eye'"
            @click="showPassword.confirm = !showPassword.confirm"
          ></i>
        </div>

        <button type="submit" :disabled="!isFormValid">Register</button>
      </form>

      <p v-if="errors.general" class="error-msg">{{ errors.general[0] }}</p>
      <p v-if="success" class="success-msg">Registration successful! Redirecting...</p>

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
      meterLookupLoading: false,
      meterLookupSuccess: false,
      success: false,
      errors: {},
      autoFilledFields: {
        first_name: false,
        last_name: false,
        phone_number: false,
        email: false
      },
      originalMeterValue: ''
    }
  },
  computed: {
    isFormValid() {
      return this.form.first_name && 
             this.form.last_name && 
             this.form.meter_number && 
             this.form.phone_number && 
             this.form.email && 
             this.form.password && 
             this.form.password_confirmation &&
             this.form.password.length >= 8 &&
             Object.keys(this.errors).length === 0;
    }
  },
  methods: {
    onMeterNumberChange() {
      // Reset auto-filled flags when meter number changes
      if (this.originalMeterValue !== this.form.meter_number) {
        this.resetAutoFilledFields();
        this.meterLookupSuccess = false;
      }
    },
    
    resetAutoFilledFields() {
      this.autoFilledFields = {
        first_name: false,
        last_name: false,
        phone_number: false,
        email: false
      };
    },
    
    async lookupMeterNumber() {
      if (!this.form.meter_number || this.form.meter_number.trim() === '') {
        this.errors.meter_number = ['Please enter a meter number'];
        return;
      }
      
      this.meterLookupLoading = true;
      this.meterLookupSuccess = false;
      this.errors.meter_number = null;
      
      try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch(`/api/meter/${this.form.meter_number}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          }
        });
        
        const data = await response.json();
        
        if (response.ok && data.found) {
          // Auto-fill the form with meter data
          this.form.first_name = data.data.first_name || '';
          this.form.last_name = data.data.last_name || '';
          this.form.phone_number = data.data.phone_number || '';
          
          // If email is provided by the client record
          if (data.data.email) {
            this.form.email = data.data.email;
          }
          
          // Mark fields as auto-filled (makes them readonly)
          this.autoFilledFields = {
            first_name: true,
            last_name: true,
            phone_number: true,
            email: data.data.email ? true : false
          };
          
          this.originalMeterValue = this.form.meter_number;
          this.meterLookupSuccess = true;
          
          // Clear any previous errors for these fields
          delete this.errors.first_name;
          delete this.errors.last_name;
          delete this.errors.phone_number;
          
          this.showTemporaryMessage('Meter verified! Your details have been auto-filled.', 'success');
        } else if (response.status === 404) {
          this.errors.meter_number = ['Meter number not found. Please contact your administrator.'];
          this.resetAutoFilledFields();
        } else if (response.status === 409) {
          this.errors.meter_number = ['This meter number is already registered. Please login instead.'];
          this.resetAutoFilledFields();
        } else {
          this.errors.meter_number = [data.message || 'Error looking up meter number'];
          this.resetAutoFilledFields();
        }
      } catch (err) {
        console.error('Lookup error:', err);
        this.errors.meter_number = ['Network error. Please try again.'];
        this.resetAutoFilledFields();
      } finally {
        this.meterLookupLoading = false;
      }
    },
    
    showTemporaryMessage(message, type) {
      const msgDiv = document.createElement('div');
      msgDiv.textContent = message;
      msgDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        border-radius: 5px;
        z-index: 1000;
        animation: slideIn 0.3s ease, fadeOut 3s 2.7s forwards;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      `;
      document.body.appendChild(msgDiv);
      setTimeout(() => msgDiv.remove(), 3000);
    },
    
    async registerUser() {
      this.loading = true;
      this.success = false;
      this.errors = {};

      // Validate password length
      if (this.form.password.length < 8) {
        this.errors.password = ['Password must be at least 8 characters'];
        this.loading = false;
        return;
      }

      // Validate password confirmation
      if (this.form.password !== this.form.password_confirmation) {
        this.errors.password_confirmation = ['Password confirmation does not match'];
        this.loading = false;
        return;
      }

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

        // Redirect after registration
        setTimeout(() => {
          window.location.href = '/user/dashboard';
        }, 1500);

      } catch (err) {
        this.errors.general = [err.message];
      } finally {
        this.loading = false;
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
              url('/images/1000019368.jpg') no-repeat center center;
  background-size: cover;
  position: relative;
}

.register-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 0;
}

.form-wrapper {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  width: 100%;
  max-width: 400px;
  position: relative;
  z-index: 1;
}

/* 🔹 Loader styles */
#global-loader {
  display: flex;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  justify-content: center;
  align-items: center;
  background: rgba(255,255,255,0.9);
  z-index: 999;
  border-radius: 12px;
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

/* 🔹 Meter number wrapper */
.meter-number-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.meter-number-wrapper input {
  flex: 1;
  padding-right: 45px;
}

.lookup-btn {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 8px;
  color: #007bff;
  width: auto;
  min-width: auto;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.lookup-btn:hover:not(:disabled) {
  background: #f0f0f0;
  transform: translateY(-50%) scale(1.05);
}

.lookup-btn:disabled {
  color: #ccc;
  cursor: not-allowed;
}

/* 🔹 Auto-filled field styling */
.auto-filled {
  background-color: #e8f0fe;
  border-color: #007bff;
  color: #0056b3;
}

/* 🔹 Success message for meter */
.meter-success {
  font-size: 0.85rem;
  margin-top: -0.5rem;
  margin-bottom: 0.5rem;
  color: #28a745;
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
  transition: all 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0,123,255,0.1);
}

input.is-invalid {
  border-color: red;
}

input.is-invalid:focus {
  box-shadow: 0 0 0 2px rgba(255,0,0,0.1);
}

input:read-only {
  cursor: default;
  background-color: #f8f9fa;
}

button[type="submit"] {
  padding: 0.75rem;
  border: none;
  background: #007bff;
  color: white;
  font-size: 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
  font-weight: 600;
}

button[type="submit"]:hover:not(:disabled) {
  background: #0056b3;
}

button[type="submit"]:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.error-msg {
  color: red;
  margin-top: -0.5rem;
  font-size: 0.85rem;
}

.success-msg {
  color: #28a745;
  margin-top: 0.5rem;
  font-size: 0.9rem;
  text-align: center;
}

.password-wrapper {
  position: relative;
}

.password-wrapper input {
  width: 100%;
  padding-right: 2.5rem;
}

.toggle-password {
  position: absolute;
  top: 50%;
  right: 0.75rem;
  transform: translateY(-50%);
  cursor: pointer;
  color: #555;
  font-size: 1.2rem;
  transition: color 0.2s ease;
}

.toggle-password:hover {
  color: #007bff;
}

p a {
  color: #007bff;
  text-decoration: none;
}

p a:hover {
  text-decoration: underline;
}

/* Animations */
@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes fadeOut {
  to {
    opacity: 0;
    visibility: hidden;
  }
}
</style>