<template>
  <div class="login-wrapper d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-sm p-4 w-100" style="max-width: 400px;">
      <h4 class="text-center mb-3">Reset Password</h4>

      <!-- OTP SEND FORM -->
      <form @submit.prevent="sendOtp" v-if="!otpSent">
        <div class="mb-3">
          <label class="form-label">Registered E-mail</label>
          <input v-model="email" type="email" class="form-control" required>
        </div>

        <!-- SEND BUTTON + SPINNER -->
        <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center"
                :disabled="sending" type="submit">
          <span v-if="sending" class="spinner-border spinner-border-sm me-2" role="status"></span>
          {{ sending ? 'Sending...' : 'Send OTP' }}
        </button>
      </form>

      <!-- RESET FORM -->
      <form @submit.prevent="resetPassword" v-else>
        <div class="mb-3"><label class="form-label">6-digit OTP</label>
          <input v-model="otp" type="text" maxlength="6" class="form-control" required>
        </div>
        <div class="mb-3"><label class="form-label">New Password</label>
          <input v-model="password" type="password" class="form-control" required>
        </div>
        <div class="mb-3"><label class="form-label">Confirm Password</label>
          <input v-model="password_confirmation" type="password" class="form-control" required>
        </div>
        <button class="btn btn-success w-100">Change Password</button>
      </form>

      <!-- MESSAGE BAR (slide-down animation) -->
      <transition name="slide">
        <div v-if="msg" class="alert mt-3" :class="err ? 'alert-danger' : 'alert-success'">
          {{ msg }}
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserResetPassword',
  data() {
    return {
      email: '',
      otp: '',
      password: '',
      password_confirmation: '',
      otpSent: false,
      msg: '',
      err: false,
      sending: false   // controls spinner + disabled state
    };
  },
  methods: {
    async sendOtp() {
      this.sending = true;          // show spinner
      this.msg = this.err = '';

      try {
        const res = await fetch('/user/password/email', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
          body: JSON.stringify({ email: this.email })
        });
        const data = await res.json();
        this.msg = data.message;
        this.err = !res.ok;

        if (res.ok && data.otpSent) {
          this.otpSent = true;
        } else {
          this.otpSent = false;
        }
      } catch (e) {
        this.msg = 'Error sending OTP';
        this.err = true;
        this.otpSent = false;
      } finally {
        this.sending = false;        // hide spinner
      }
    },

    async resetPassword() {
      this.msg = '';
      this.err = false;
      try {
        const res = await fetch('/user/password/reset', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
          body: JSON.stringify({
            email: this.email,
            otp: this.otp,
            password: this.password,
            password_confirmation: this.password_confirmation
          })
        });
        const data = await res.json();
        this.msg = data.message;
        this.err = !res.ok;
        if (res.ok) setTimeout(() => this.$router.push('/user/login'), 1500);
      } catch (e) {
        this.msg = 'Reset failed';
        this.err = true;
      }
    }
  }
};

function csrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}
</script>

<style scoped>
/* slide-down for message bar */
.slide-enter-active, .slide-leave-active {
  transition: all .3s ease;
}
.slide-enter-from, .slide-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>