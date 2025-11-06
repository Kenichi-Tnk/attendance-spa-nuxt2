// 認証状態の復元とAxios設定
export default function ({ store, $axios }) {
  // ページ読み込み時に認証状態を復元
  if (process.client) {
    store.dispatch('auth/restoreAuth')
  }

  // リクエストインターセプター
  $axios.onRequest(config => {
    console.log('Request interceptor - Making request to:', config.url)
    console.log('Request interceptor - Base URL:', config.baseURL)
    console.log('Request interceptor - Full URL:', config.baseURL + config.url)
    console.log('Request interceptor - Method:', config.method)
    console.log('Request interceptor - Start time:', new Date().toISOString())
    
    const token = store.state.auth.token
    if (token) {
      config.headers.common['Authorization'] = `Bearer ${token}`
      console.log('Request interceptor - Added auth token')
    }
    
    return config
  })

  // レスポンスインターセプター（成功）
  $axios.onResponse(response => {
    console.log('Response interceptor - Response received:', response.status)
    console.log('Response interceptor - Response time:', new Date().toISOString())
    return response
  })

  // レスポンスエラーインターセプター
  $axios.onError(error => {
    console.error('Response interceptor - Error occurred:', error.message)
    console.error('Response interceptor - Error time:', new Date().toISOString())
    console.error('Response interceptor - Error config:', error.config)
    
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