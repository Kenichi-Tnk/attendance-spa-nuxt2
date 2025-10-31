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
    
    <form @submit.prevent="handleRegister">
      <div class="auth__form-container">
        <!-- 名前 -->
        <div class="auth__field-group">
          <label for="name" class="auth__field-label">
            お名前
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="auth__field-input"
            placeholder="田中太郎"
          />
        </div>
        
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
            placeholder="パスワード（8文字以上）"
          />
        </div>
        
        <!-- パスワード確認 -->
        <div class="auth__field-group">
          <label for="password_confirmation" class="auth__field-label">
            パスワード確認
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="auth__field-input"
            placeholder="パスワード（確認）"
          />
        </div>
        
        <!-- 登録ボタン -->
        <div>
          <button
            type="submit"
            :disabled="$store.getters['auth/isLoading']"
            class="auth__btn auth__btn--primary"
          >
            <span v-if="$store.getters['auth/isLoading']">登録中...</span>
            <span v-else>新規登録</span>
          </button>
        </div>
      </div>
    </form>
    
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
      
      // パスワード確認チェック
      if (this.form.password !== this.form.password_confirmation) {
        this.errorMessage = 'パスワードが一致しません'
        return
      }
      
      try {
        const result = await this.$store.dispatch('auth/register', this.form)
        console.log('Registration result:', result)
        
        if (result.success) {
          this.successMessage = result.message
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