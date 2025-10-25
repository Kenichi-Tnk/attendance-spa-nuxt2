&lt;template&gt;
  &lt;div&gt;
    &lt;p&gt;リダイレクト中...&lt;/p&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
  name: 'IndexPage',
  middleware: ['auth', 'verified'],
  
  mounted() {
    // 管理者は管理者ダッシュボードへ、一般ユーザーはダッシュボードへ
    if (this.$store.getters['auth/isAdmin']) {
      this.$router.push('/admin/dashboard')
    } else {
      this.$router.push('/dashboard')
    }
  }
}
&lt;/script&gt;