<template>
  <div>
    <h2 class="auth__title">
      ログイン
    </h2>
    
    <!-- エラーメッセージ -->
    <div v-if="errorMessage" class="auth__message auth__message--error">
      {{ errorMessage }}
    </div>
    
    <form @submit.prevent="handleLogin">
      <div class="auth__form-container">
        <!-- メールアドレス -->
        <div class="auth__field-group">
          <label for="email" class="auth__field-label">
            メールアドレス
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="auth__field-input"
            placeholder="email@example.com"
          />
        </div>
        
        <!-- パスワード -->
        <div class="auth__field-group">
          <label for="password" class="auth__field-label">
            パスワード
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="auth__field-input"
            placeholder="パスワード"
          />
        </div>
        
        <!-- ログインボタン -->
        <div>
          <button
            type="submit"
            :disabled="$store.getters['auth/isLoading']"
            class="auth__btn auth__btn--primary"
          >
            <span v-if="$store.getters['auth/isLoading']">ログイン中...</span>
            <span v-else>ログイン</span>
          </button>
        </div>
      </div>
    </form>
    
    <!-- 登録リンク -->
    <div class="auth__link-section">
      <p class="auth__link-text">
        アカウントをお持ちでない方は
        <NuxtLink to="/register" class="auth__link">
          新規登録
        </NuxtLink>
      </p>
    </div>
    
    <!-- デモ用認証情報 -->
    <div class="login__demo-section">
      <h3 class="login__demo-title">デモ用ログイン情報</h3>
      <div class="login__demo-content">
        <p><strong>一般ユーザー:</strong> user@example.com / password</p>
        <p><strong>管理者:</strong> admin@example.com / password</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  layout: 'auth',
  middleware: 'guest',
  
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errorMessage: ''
    }
  },
  
  methods: {
    async handleLogin() {
      this.errorMessage = ''
      console.log('Login form data:', this.form)
      
      try {
        const result = await this.$store.dispatch('auth/login', this.form)
        console.log('Login result:', result)
        
        if (result.success) {
          // リダイレクト先の取得
          const redirectTo = this.$route.query.redirect || '/'
          this.$router.push(redirectTo)
        } else {
          this.errorMessage = result.error
          console.error('Login failed:', result.error)
        }
      } catch (error) {
        console.error('Login exception:', error)
        this.errorMessage = 'ログインに失敗しました'
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/auth.css';

/* Login-specific styles */
.login__demo-section {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 0.375rem;
}

.login__demo-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e40af;
  margin-bottom: 0.5rem;
}

.login__demo-content {
  font-size: 0.75rem;
  color: #1d4ed8;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.login__demo-content p {
  margin: 0;
}
</style>