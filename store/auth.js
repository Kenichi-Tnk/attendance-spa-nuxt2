export const state = () => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: false
})

export const mutations = {
  SET_USER(state, user) {
    state.user = user
    state.isAuthenticated = !!user
  },
  
  SET_TOKEN(state, token) {
    state.token = token
  },
  
  SET_LOADING(state, loading) {
    state.isLoading = loading
  },
  
  LOGOUT(state) {
    state.user = null
    state.token = null
    state.isAuthenticated = false
  }
}

export const actions = {
  // モックログイン（後でAPI接続）
  async login({ commit }, credentials) {
    try {
      commit('SET_LOADING', true)
      
      // TODO: 後でAPI呼び出しに置き換え
      await new Promise(resolve => setTimeout(resolve, 1000)) // API呼び出しのシミュレーション
      
      // モックレスポンス
      const mockResponse = {
        user: {
          id: 1,
          name: credentials.email === 'admin@example.com' ? '管理者' : '田中健一',
          email: credentials.email,
          role: credentials.email === 'admin@example.com' ? 'admin' : 'user',
          email_verified_at: '2024-01-01T00:00:00.000Z'
        },
        token: 'mock-jwt-token-12345'
      }
      
      // 認証情報をlocalStorageに保存
      localStorage.setItem('auth-token', mockResponse.token)
      localStorage.setItem('auth-user', JSON.stringify(mockResponse.user))
      
      commit('SET_USER', mockResponse.user)
      commit('SET_TOKEN', mockResponse.token)
      
      return { success: true, user: mockResponse.user }
    } catch (error) {
      return { success: false, error: 'ログインに失敗しました' }
    } finally {
      commit('SET_LOADING', false)
    }
  },
  
  // モック登録
  async register({ commit }, userData) {
    try {
      commit('SET_LOADING', true)
      
      // TODO: 後でAPI呼び出しに置き換え
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      const mockUser = {
        id: Date.now(),
        name: userData.name,
        email: userData.email,
        role: 'user',
        email_verified_at: null // メール認証前
      }
      
      return { success: true, message: '登録が完了しました。メール認証を行ってください。' }
    } catch (error) {
      return { success: false, error: '登録に失敗しました' }
    } finally {
      commit('SET_LOADING', false)
    }
  },
  
  // ログアウト
  async logout({ commit }) {
    try {
      // TODO: 後でAPI呼び出しに置き換え
      localStorage.removeItem('auth-token')
      localStorage.removeItem('auth-user')
      
      commit('LOGOUT')
      return { success: true }
    } catch (error) {
      return { success: false, error: 'ログアウトに失敗しました' }
    }
  },
  
  // 認証状態の復元（ページリロード時）
  restoreAuth({ commit }) {
    const token = localStorage.getItem('auth-token')
    const user = localStorage.getItem('auth-user')
    
    if (token && user) {
      try {
        commit('SET_TOKEN', token)
        commit('SET_USER', JSON.parse(user))
      } catch (error) {
        localStorage.removeItem('auth-token')
        localStorage.removeItem('auth-user')
      }
    }
  }
}

export const getters = {
  isAuthenticated: state => state.isAuthenticated,
  user: state => state.user,
  token: state => state.token,
  isAdmin: state => state.user?.role === 'admin',
  isEmailVerified: state => !!state.user?.email_verified_at,
  isLoading: state => state.isLoading
}