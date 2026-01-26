<template>
  <div>
    <h2 class="auth__title">
      ログイン
    </h2>

    <!-- エラーメッセージ -->
    <div v-if="errorMessage" class="auth__message auth__message--error">
      {{ errorMessage }}
    </div>

    <ValidationObserver v-slot="{ handleSubmit, invalid }">
      <form @submit.prevent="handleSubmit(handleLogin)">
        <div class="auth__form-container">
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
              >
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
            >
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="auth__field-input"
                :class="{ 'auth__field-input--error': errors.length > 0 }"
                placeholder="パスワード"
              >
              <span v-if="errors.length > 0" class="auth__field-error">
                {{ errors[0] }}
              </span>
            </ValidationProvider>
          </div>

          <!-- ログインボタン -->
          <div>
            <button
              type="submit"
              :disabled="$store.getters['auth/isLoading'] || invalid"
              class="auth__btn auth__btn--primary"
            >
              <span v-if="$store.getters['auth/isLoading']">ログイン中...</span>
              <span v-else>ログイン</span>
            </button>
          </div>
        </div>
      </form>
    </ValidationObserver>

    <!-- 登録リンク -->
    <div class="auth__link-section">
      <p class="auth__link-text">
        アカウントをお持ちでない方は
        <NuxtLink to="/register" class="auth__link">
          新規登録
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  layout: 'auth',
  middleware: 'guest',

  data () {
    return {
      form: {
        email: '',
        password: ''
      },
      errorMessage: ''
    }
  },

  methods: {
    async handleLogin () {
      this.errorMessage = ''

      try {
        const result = await this.$store.dispatch('auth/login', this.form)

        if (result.success) {
          // 認証状態が完全にセットされるまで待つ
          await this.$nextTick()

          // リダイレクト先の取得
          const redirectTo = this.$route.query.redirect || '/'

          // $router.pushでリダイレクト
          this.$router.push(redirectTo)
        } else {
          this.errorMessage = result.error
        }
      } catch (error) {
        this.errorMessage = 'ログインに失敗しました'
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/auth.css';
</style>
