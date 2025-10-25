export default function ({ store, redirect }) {
  // 認証状態の復元
  if (process.client && !store.getters['auth/isAuthenticated']) {
    store.dispatch('auth/restoreAuth')
  }
  
  // 認証済みの場合はダッシュボードにリダイレクト
  if (store.getters['auth/isAuthenticated']) {
    return redirect('/')
  }
}