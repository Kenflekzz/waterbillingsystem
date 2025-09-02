<template>
  <div class="bg-primary vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-5">
          <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
              <h3 class="text-center font-weight-light my-4">Admin Login</h3>
            </div>
            <div class="card-body">
              <form @submit.prevent="login">
                <div class="form-floating mb-3">
                  <input
                    v-model="email"
                    type="email"
                    class="form-control"
                    id="email"
                    placeholder="name@example.com"
                    required
                  />
                  <label for="email">Email address</label>
                </div>
                <div class="form-floating mb-3">
                  <input
                    v-model="password"
                    type="password"
                    class="form-control"
                    id="password"
                    placeholder="Password"
                    required
                  />
                  <label for="password">Password</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" id="remember" type="checkbox" />
                  <label class="form-check-label" for="remember">Remember Password</label>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                  <a class="small" href="#">Forgot Password?</a>
                  <button type="submit" class="btn btn-primary">Login</button>
                </div>
                <div v-if="error" class="alert alert-danger mt-3" role="alert">
                  {{ error }}
                </div>
              </form>
            </div>
            <div class="card-footer text-center py-3">
              <div class="small"><a href="/admin/register">Need an account? Sign up!</a></div>
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password
          })
        });

        if (!response.ok) {
          const errorData = await response.json();
          this.error = errorData.message || 'Invalid credentials.';
          return;
        }

        window.location.href = '/admin/dashboard';
      } catch (err) {
        this.error = 'Login failed: ' + err.message;
      }
    }
  }
};
</script>

<style scoped>
.bg-primary {
  padding-top: 40px;
  padding-bottom: 40px;
  min-height: 100vh;
  background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('/images/Flag_of_Magallanes,_Agusan_del_Norte.webp') no-repeat center center;
  background-size: cover;
}
</style>
