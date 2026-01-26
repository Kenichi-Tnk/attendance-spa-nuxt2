export default function ({ store, redirect }) {
  // 認証チェック
  if (!store.getters['auth/isAuthenticated']) {
    return redirect('/login')
  }

  // 管理者権限チェック
  if (!store.getters['auth/isAdmin']) {
    return redirect('/')
  }
}
