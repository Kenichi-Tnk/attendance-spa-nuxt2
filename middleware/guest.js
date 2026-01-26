export default async function ({ store, redirect }) {
  // 認証状態の復元（完了を待つ）
  if (process.client && !store.getters['auth/isAuthenticated']) {
    await store.dispatch('auth/restoreAuth')
  }

  // 認証済みの場合はダッシュボードにリダイレクト
  if (store.getters['auth/isAuthenticated']) {
    return redirect('/')
  }
}
