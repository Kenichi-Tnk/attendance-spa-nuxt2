<template>
  <div class="default-layout__container">
    <!-- ヘッダー -->
    <nav class="default-layout__header">
      <div class="default-layout__nav-container">
        <div class="default-layout__nav-content">
          <div class="default-layout__brand-section">
            <NuxtLink to="/" class="default-layout__brand-link">
              勤怠管理システム
            </NuxtLink>
          </div>

          <!-- ナビゲーション -->
          <div class="default-layout__nav-menu">
            <NuxtLink to="/" class="default-layout__nav-link">
              ダッシュボード
            </NuxtLink>
            <NuxtLink to="/attendance" class="default-layout__nav-link">
              勤怠一覧
            </NuxtLink>
            <NuxtLink to="/correction-requests" class="default-layout__nav-link">
              修正申請一覧
            </NuxtLink>
            <NuxtLink v-if="$store.getters['auth/isAdmin']" to="/admin" class="default-layout__nav-link">
              管理者画面
            </NuxtLink>
          </div>

          <!-- ユーザーメニュー -->
          <div class="default-layout__user-menu">
            <span class="default-layout__user-name">
              {{ $store.getters['auth/user']?.name }}さん
            </span>
            <button @click="logout" class="default-layout__logout-btn">
              ログアウト
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- メインコンテンツ -->
    <main class="default-layout__main">
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