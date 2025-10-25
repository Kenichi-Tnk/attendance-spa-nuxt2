<template>
  <div class="min-h-screen bg-gray-50">
    <!-- ヘッダー -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <NuxtLink to="/" class="text-xl font-semibold text-gray-900">
              勤怠管理システム
            </NuxtLink>
          </div>
          
          <!-- ナビゲーション -->
          <div class="hidden md:flex items-center space-x-4">
            <NuxtLink to="/" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
              ダッシュボード
            </NuxtLink>
            <NuxtLink to="/attendance" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
              勤怠一覧
            </NuxtLink>
            <NuxtLink v-if="$store.getters['auth/isAdmin']" to="/admin" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
              管理者画面
            </NuxtLink>
          </div>
          
          <!-- ユーザーメニュー -->
          <div class="flex items-center space-x-4">
            <span class="text-gray-700 text-sm">
              {{ $store.getters['auth/user']?.name }}さん
            </span>
            <button @click="logout" class="text-red-600 hover:text-red-800 text-sm font-medium">
              ログアウト
            </button>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <Nuxt />
    </main>
  </div>
</template>

<script>
export default {
  methods: {
    async logout() {
      const result = await this.$store.dispatch('auth/logout')
      if (result.success) {
        this.$router.push('/login')
      }
    }
  }
}
</script>