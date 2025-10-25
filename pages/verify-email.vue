&lt;template&gt;
  &lt;div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"&gt;
    &lt;div class="max-w-md w-full space-y-8"&gt;
      &lt;div&gt;
        &lt;h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900"&gt;
          メール認証が必要です
        &lt;/h2&gt;
        &lt;p class="mt-2 text-center text-sm text-gray-600"&gt;
          登録されたメールアドレスに認証リンクを送信しました
        &lt;/p&gt;
      &lt;/div&gt;
      
      &lt;div class="rounded-md shadow-sm space-y-4"&gt;
        &lt;div class="text-center"&gt;
          &lt;p class="text-gray-700"&gt;
            認証メールが届いていない場合は、下記ボタンから再送信できます
          &lt;/p&gt;
        &lt;/div&gt;
        
        &lt;div&gt;
          &lt;button
            @click="resendVerificationEmail"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          &gt;
            &lt;span v-if="isLoading"&gt;送信中...&lt;/span&gt;
            &lt;span v-else&gt;認証メールを再送信&lt;/span&gt;
          &lt;/button&gt;
        &lt;/div&gt;
        
        &lt;div v-if="message" class="text-center"&gt;
          &lt;p class="text-green-600"&gt;{{ message }}&lt;/p&gt;
        &lt;/div&gt;
        
        &lt;div class="text-center"&gt;
          &lt;button
            @click="$store.dispatch('auth/logout')"
            class="text-indigo-600 hover:text-indigo-500"
          &gt;
            ログアウト
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
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
        await new Promise(resolve =&gt; setTimeout(resolve, 1000))
        
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
&lt;/script&gt;