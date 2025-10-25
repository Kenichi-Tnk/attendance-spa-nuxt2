<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          メール認証が必要です
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          登録されたメールアドレスに認証リンクを送信しました
        </p>
      </div>
      
      <div class="rounded-md shadow-sm space-y-4">
        <div class="text-center">
          <p class="text-gray-700">
            認証メールが届いていない場合は、下記ボタンから再送信できます
          </p>
        </div>
        
        <div>
          <button
            @click="resendVerificationEmail"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="isLoading">送信中...</span>
            <span v-else>認証メールを再送信</span>
          </button>
        </div>
        
        <div v-if="message" class="text-center">
          <p class="text-green-600">{{ message }}</p>
        </div>
        
        <div class="text-center">
          <button
            @click="$store.dispatch('auth/logout')"
            class="text-indigo-600 hover:text-indigo-500"
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
        
        // TODO: API呼び出しでメール再送信
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        this.message = '認証メールを再送信しました'
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