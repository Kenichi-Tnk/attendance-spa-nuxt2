<template>
  <div class="verify-email__container">
    <div class="verify-email__content">
      <div>
        <h2 class="auth__title auth__title--large">
          メール認証が必要です
        </h2>
        <p class="auth__subtitle">
          ご登録ありがとうございます。メールアドレスの認証を完了してください
        </p>
      </div>

      <div class="verify-email__actions">
        <div class="verify-email__description">
          <p>
            下記のボタンをクリックして、認証メールを送信してください
          </p>
        </div>

        <div>
          <button
            @click="resendVerificationEmail"
            :disabled="isLoading"
            class="auth__btn auth__btn--primary"
          >
            <span v-if="isLoading">送信中...</span>
            <span v-else>確認メールを送信</span>
          </button>
        </div>

        <div v-if="message" class="verify-email__message">
          <p :class="message.includes('エラー') ? 'verify-email__message--error' : 'verify-email__message--success'">{{ message }}</p>
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

        const response = await this.$axios.$post('/api/email/verification-notification')
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