<template>
  <div class="register-wrapper  min-vh-100 d-flex justify-content-center align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-7">
          <div class="card shadow-lg border-0 rounded-lg mt-5" style="background-color: rgba(255,255,255, 0.1); backdrop-filter: 50px;">
            <div class="card-header">
              <h3 class="text-center font-weight-light text-white my-4">Create Admin Account</h3>
            </div>
            <div class="card-body">
              <form @submit.prevent="register">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <div class="form-floating mb-3 mb-md-0">
                      <input
                        v-model="first_name"
                        class="form-control"
                        id="inputFirstName"
                        type="text"
                        placeholder="Enter your first name"
                        required
                      />
                      <label for="inputFirstName">First name</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input
                        v-model="last_name"
                        class="form-control"
                        id="inputLastName"
                        type="text"
                        placeholder="Enter your last name"
                        required
                      />
                      <label for="inputLastName">Last name</label>
                    </div>
                  </div>
                </div>
                <div class="form-floating mb-3">
                  <input
                    v-model="email"
                    class="form-control"
                    id="inputEmail"
                    type="email"
                    placeholder="name@example.com"
                    required
                  />
                  <label for="inputEmail">Email address</label>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <div class="form-floating mb-3 mb-md-0">
                      <input
                        v-model="password"
                        class="form-control"
                        id="inputPassword"
                        type="password"
                        placeholder="Create a password"
                        required
                      />
                      <label for="inputPassword">Password</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating mb-3 mb-md-0">
                      <input
                        v-model="password_confirmation"
                        class="form-control"
                        id="inputPasswordConfirm"
                        type="password"
                        placeholder="Confirm password"
                        required
                      />
                      <label for="inputPasswordConfirm">Confirm Password</label>
                    </div>
                  </div>
                </div>
                <div class="d-grid mt-3">
                  <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
                <div v-if="error" class="alert alert-danger mt-3" role="alert">
                  {{ error }}
                </div>
              </form>
            </div>
            <div class="card-footer text-center py-3">
              <div class="small"><a href="/admin/login">Have an account? Go to login</a></div>
            </div>
          </div>
        </div>
      </div>
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
        const response = await fetch('/api/admin/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
.register-wrapper {
  padding-top: 40px;
  padding-bottom: 40px;
  background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),url('/images/Flag_of_Magallanes,_Agusan_del_Norte.webp') no-repeat center center;
  background-size: cover;
}
</style>