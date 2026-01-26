<template>
  <div class="verify-email__container">
    <div class="verify-email__content">
      <LoadingSpinner v-if="isVerifying" message="メールアドレスを認証しています..." />

      <div v-else-if="verificationSuccess" class="verify-email__success">
        <div class="verify-email__icon verify-email__icon--success">
          ✓
        </div>
        <h2 class="auth__title">
          メール認証完了
        </h2>
        <p class="auth__subtitle">
          メールアドレスの認証が完了しました
        </p>
        <p class="auth__subtitle">
          2秒後に自動的にホームへ移動します...
        </p>
      </div>

      <div v-else class="verify-email__error">
        <div class="verify-email__icon verify-email__icon--error">
          ✗
        </div>
        <h2 class="auth__title">
          認証に失敗しました
        </h2>
        <p class="auth__subtitle">
          {{ errorMessage }}
        </p>
        <button
          class="auth__btn auth__btn--primary"
          @click="$router.push('/verify-email')"
        >
          メール認証画面へ戻る
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EmailVerifyPage',
  layout: 'auth',

  data () {
    return {
      isVerifying: true,
      verificationSuccess: false,
      errorMessage: ''
    }
  },

  async mounted () {
    await this.verifyEmail()
  },

  methods: {
    async verifyEmail () {
      try {
        const { id, hash } = this.$route.params
        const { expires, signature } = this.$route.query

        const response = await this.$axios.$get(`/api/email/verify/${id}/${hash}`, {
          params: { expires, signature }
        })

        this.verificationSuccess = true

        // 少し待ってからユーザー情報を再取得（DBの更新が反映されるまで）
        await new Promise(resolve => setTimeout(resolve, 500))

        const updatedUser = await this.$store.dispatch('auth/fetchUser')

        // localStorageを確実に更新
        if (updatedUser) {
          localStorage.setItem('auth-user', JSON.stringify(updatedUser))
          this.$store.commit('auth/SET_USER', updatedUser)
        }

        // 1.5秒後にホームへリダイレクト
        setTimeout(() => {
          this.$router.push('/')
        }, 1500)
      } catch (error) {
        console.error('Email verification error:', error)
        this.verificationSuccess = false

        if (error.response?.data?.message) {
          this.errorMessage = error.response.data.message
        } else {
          this.errorMessage = '認証リンクが無効または期限切れです'
        }
      } finally {
        this.isVerifying = false
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/auth.css';

.verify-email__icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.verify-email__icon--success {
  background-color: #d4edda;
  color: #155724;
}

.verify-email__icon--error {
  background-color: #f8d7da;
  color: #721c24;
}

.verify-email__success,
.verify-email__error {
  text-align: center;
}
</style>
