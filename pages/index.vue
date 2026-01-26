<template>
  <div>
    <p>リダイレクト中...</p>
  </div>
</template>

<script>
export default {
  name: 'IndexPage',
  middleware: ['auth', 'verified'],

  mounted () {
    // 管理者は管理者ダッシュボードへ、一般ユーザーはダッシュボードへ
    if (this.$store.getters['auth/isAdmin']) {
      this.$router.push('/admin/dashboard')
    } else {
      this.$router.push('/dashboard')
    }
  }
}
</script>
