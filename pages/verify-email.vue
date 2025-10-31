<template>
  <div class="verify-email__container">
    <div class="verify-email__content">
      <div>
        <h2 class="auth__title auth__title--large">
          メール認証が必要です
        </h2>
        <p class="auth__subtitle">
          登録されたメールアドレスに認証リンクを送信しました
        </p>
      </div>

      <div class="verify-email__actions">
        <div class="verify-email__description">
          <p>
            認証メールが届いていない場合は、下記ボタンから再送信できます
          </p>
        </div>

        <div>
          <button
            @click="resendVerificationEmail"
            :disabled="isLoading"
            class="auth__btn auth__btn--primary"
          >
            <span v-if="isLoading">送信中...</span>
            <span v-else>認証メールを再送信</span>
          </button>
        </div>

        <div v-if="message" class="verify-email__message">
          <p :class="message.includes('エラー') ? 'verify-email__message--error' : 'verify-email__message--success'">{{ message }}</p>
        </div>

        <div class="verify-email__message">
          <button
            @click="$store.dispatch('auth/logout')"
            class="auth__btn--link"
          >
            ログアウト
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VerifyEmailPage',
  layout: 'auth',
  middleware: ['auth'],

  data() {
    return {
      isLoading: false,
      message: ''
    }
  },

  methods: {
    async resendVerificationEmail() {
      try {
        this.isLoading = true
        this.message = ''

        const response = await this.$axios.$post('/email/verification-notification')

        this.message = response.message || '認証メールを再送信しました'
      } catch (error) {
        console.error('メール再送信エラー:', error)
        this.message = 'エラーが発生しました。しばらくしてから再度お試しください。'
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/auth.css';
</style>