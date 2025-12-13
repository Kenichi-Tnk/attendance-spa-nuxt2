<template>
  <div>
    <h2 class="auth__title">
      新規登録
    </h2>
    
    <!-- 成功メッセージ -->
    <div v-if="successMessage" class="auth__message auth__message--success">
      {{ successMessage }}
    </div>
    
    <!-- エラーメッセージ -->
    <div v-if="errorMessage" class="auth__message auth__message--error">
      {{ errorMessage }}
    </div>
    
    <ValidationObserver v-slot="{ handleSubmit, invalid }">
      <form @submit.prevent="handleSubmit(handleRegister)">
        <div class="auth__form-container">
          <!-- 名前 -->
          <div class="auth__field-group">
            <label for="name" class="auth__field-label">
              お名前
            </label>
            <ValidationProvider
              v-slot="{ errors }"
              name="name"
              rules="required|max:255"
            >
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="auth__field-input"
                :class="{ 'auth__field-input--error': errors.length > 0 }"
                placeholder="田中太郎"
              />
              <span v-if="errors.length > 0" class="auth__field-error">
                {{ errors[0] }}
              </span>
            </ValidationProvider>
          </div>
          
          <!-- メールアドレス -->
          <div class="auth__field-group">
            <label for="email" class="auth__field-label">
              メールアドレス
            </label>
            <ValidationProvider
              v-slot="{ errors }"
              name="email"
              rules="required|email"
            >
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="auth__field-input"
                :class="{ 'auth__field-input--error': errors.length > 0 }"
                placeholder="email@example.com"
              />
              <span v-if="errors.length > 0" class="auth__field-error">
                {{ errors[0] }}
              </span>
            </ValidationProvider>
          </div>
          
          <!-- パスワード -->
          <div class="auth__field-group">
            <label for="password" class="auth__field-label">
              パスワード
            </label>
            <ValidationProvider
              v-slot="{ errors }"
              name="password"
              rules="required|min:8"
              vid="password"
            >
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="auth__field-input"
                :class="{ 'auth__field-input--error': errors.length > 0 }"
                placeholder="パスワード（8文字以上）"
              />
              <span v-if="errors.length > 0" class="auth__field-error">
                {{ errors[0] }}
              </span>
            </ValidationProvider>
          </div>
          
          <!-- パスワード確認 -->
          <div class="auth__field-group">
            <label for="password_confirmation" class="auth__field-label">
              パスワード確認
            </label>
            <ValidationProvider
              v-slot="{ errors }"
              name="password_confirmation"
              rules="required|confirmed:password"
            >
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="auth__field-input"
                :class="{ 'auth__field-input--error': errors.length > 0 }"
                placeholder="パスワード（確認）"
              />
              <span v-if="errors.length > 0" class="auth__field-error">
                {{ errors[0] }}
              </span>
            </ValidationProvider>
          </div>
          
          <!-- 登録ボタン -->
          <div>
            <button
              type="submit"
              :disabled="$store.getters['auth/isLoading'] || invalid"
              class="auth__btn auth__btn--primary"
            >
              <span v-if="$store.getters['auth/isLoading']">登録中...</span>
              <span v-else>新規登録</span>
            </button>
          </div>
        </div>
      </form>
    </ValidationObserver>
    
    <!-- ログインリンク -->
    <div class="auth__link-section">
      <p class="auth__link-text">
        すでにアカウントをお持ちの方は
        <NuxtLink to="/login" class="auth__link">
          ログイン
        </NuxtLink>
      </p>
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
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      errorMessage: '',
      successMessage: ''
    }
  },
  
  methods: {
    async handleRegister() {
      this.errorMessage = ''
      this.successMessage = ''
      console.log('Registration form data:', this.form)
      
      try {
        const result = await this.$store.dispatch('auth/register', this.form)
        console.log('Registration result:', result)
        
        if (result.success) {
          this.successMessage = result.message
          
          // メール認証ページへリダイレクト
          if (result.shouldRedirectToVerify) {
            setTimeout(() => {
              this.$router.push('/verify-email')
            }, 1500) // 1.5秒後にリダイレクト（成功メッセージを表示してから）
          }
          
          // フォームをクリア
          this.form = {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
          }
        } else {
          this.errorMessage = result.error
          console.error('Registration failed:', result.error)
        }
      } catch (error) {
        console.error('Registration exception:', error)
        this.errorMessage = '登録に失敗しました'
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/auth.css';
</style>