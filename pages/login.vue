<template>
  <div>
    <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">
      ログイン
    </h2>
    
    <!-- エラーメッセージ -->
    <div v-if="errorMessage" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
      {{ errorMessage }}
    </div>
    
    <form @submit.prevent="handleLogin">
      <div class="space-y-6">
        <!-- メールアドレス -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            メールアドレス
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="email@example.com"
          />
        </div>
        
        <!-- パスワード -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            パスワード
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="パスワード"
          />
        </div>
        
        <!-- ログインボタン -->
        <div>
          <button
            type="submit"
            :disabled="$store.getters['auth/isLoading']"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="$store.getters['auth/isLoading']">ログイン中...</span>
            <span v-else>ログイン</span>
          </button>
        </div>
      </div>
    </form>
    
    <!-- 登録リンク -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        アカウントをお持ちでない方は
        <NuxtLink to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
          新規登録
        </NuxtLink>
      </p>
    </div>
    
    <!-- デモ用認証情報 -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
      <h3 class="text-sm font-medium text-blue-800 mb-2">デモ用ログイン情報</h3>
      <div class="text-xs text-blue-700 space-y-1">
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
      
      try {
        const result = await this.$store.dispatch('auth/login', this.form)
        
        if (result.success) {
          // リダイレクト先の取得
          const redirectTo = this.$route.query.redirect || '/'
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