export default function ({ store, redirect }) {
  // 認証チェック
  if (!store.getters['auth/isAuthenticated']) {
    return redirect('/login')
  }
  
  // メール認証チェック
  if (!store.getters['auth/isEmailVerified']) {
    return redirect('/verify-email')
  }
}