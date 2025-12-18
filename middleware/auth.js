export default async function ({ store, redirect, route }) {
  console.log('auth middleware - start', {
    isAuthenticated: store.getters['auth/isAuthenticated'],
    user: store.state.auth.user,
    token: store.state.auth.token ? 'exists' : 'null',
    path: route.fullPath
  })
  
  // 認証状態の復元(完了を待つ)
  if (process.client && !store.getters['auth/isAuthenticated']) {
    console.log('auth middleware - restoring auth...')
    await store.dispatch('auth/restoreAuth')
    console.log('auth middleware - after restore:', {
      isAuthenticated: store.getters['auth/isAuthenticated'],
      user: store.state.auth.user,
      token: store.state.auth.token ? 'exists' : 'null'
    })
  }
  
  // 認証されていない場合はログインページにリダイレクト
  if (!store.getters['auth/isAuthenticated']) {
    console.log('auth middleware - not authenticated, redirecting to login')
    return redirect('/login?redirect=' + encodeURIComponent(route.fullPath))
  }
  
  console.log('auth middleware - authenticated, proceeding')
}