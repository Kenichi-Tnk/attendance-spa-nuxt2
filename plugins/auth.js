// 認証状態の復元とAxios設定
export default function ({ store, $axios }) {
  // ページ読み込み時に認証状態を復元
  if (process.client) {
    store.dispatch('auth/restoreAuth')
  }

  // リクエストインターセプター
  $axios.onRequest(config => {
    const token = store.state.auth.token
    if (token) {
      config.headers.common['Authorization'] = `Bearer ${token}`
    }
  })

  // レスポンスエラーインターセプター
  $axios.onError(error => {
    if (error.response?.status === 401) {
      // 認証エラーの場合はログアウト
      store.dispatch('auth/logout')
      
      // ログインページにリダイレクト
      if (process.client && window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    
    return Promise.reject(error)
  })
}