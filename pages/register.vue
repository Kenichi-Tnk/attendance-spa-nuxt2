<template>
  <div>
    <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">
      新規登録
    </h2>
    
    <!-- 成功メッセージ -->
    <div v-if="successMessage" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
      {{ successMessage }}
    </div>
    
    <!-- エラーメッセージ -->
    <div v-if="errorMessage" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
      {{ errorMessage }}
    </div>
    
    <form @submit.prevent="handleRegister">
      <div class="space-y-6">
        <!-- 名前 -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">
            お名前
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="田中太郎"
          />
        </div>
        
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
            placeholder="パスワード（8文字以上）"
          />
        </div>
        
        <!-- パスワード確認 -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            パスワード確認
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="パスワード（確認）"
          />
        </div>
        
        <!-- 登録ボタン -->
        <div>
          <button
            type="submit"
            :disabled="$store.getters['auth/isLoading']"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="$store.getters['auth/isLoading']">登録中...</span>
            <span v-else>新規登録</span>
          </button>
        </div>
      </div>
    </form>
    
    <!-- ログインリンク -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        すでにアカウントをお持ちの方は
        <NuxtLink to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
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
      
      // パスワード確認チェック
      if (this.form.password !== this.form.password_confirmation) {
        this.errorMessage = 'パスワードが一致しません'
        return
      }
      
      try {
        const result = await this.$store.dispatch('auth/register', this.form)
        
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
        }
      } catch (error) {
        this.errorMessage = '登録に失敗しました'
      }
    }
  }
}
</script>