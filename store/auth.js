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

export const getters = {
  isAuthenticated: state => state.isAuthenticated,
  isLoading: state => state.isLoading,
  user: state => state.user,
  token: state => state.token,
  isEmailVerified: state => state.user?.email_verified_at !== null,
  isAdmin: state => state.user?.is_admin === true
}

export const actions = {
  // API ログイン
  async login({ commit }, credentials) {
    try {
      commit('SET_LOADING', true)
      console.log('Attempting login with:', credentials)
      
      const response = await this.$axios.$post('/login', {
        email: credentials.email,
        password: credentials.password
      })
      
      console.log('Login response:', response)
      
      // 認証情報をlocalStorageに保存
      localStorage.setItem('auth-token', response.token)
      localStorage.setItem('auth-user', JSON.stringify(response.user))
      
      // Axiosのデフォルトヘッダーにトークンを設定
      this.$axios.setToken(response.token, 'Bearer')
      
      commit('SET_USER', response.user)
      commit('SET_TOKEN', response.token)
      
      return { success: true, user: response.user }
    } catch (error) {
      console.error('Login error:', error)
      console.error('Error response:', error.response)
      const message = error.response?.data?.message || 'ログインに失敗しました'
      return { success: false, error: message }
    } finally {
      commit('SET_LOADING', false)
    }
  },
  
  // API 登録
  async register({ commit }, userData) {
    try {
      commit('SET_LOADING', true)
      console.log('Attempting registration with:', userData)
      
      const response = await this.$axios.$post('/register', {
        name: userData.name,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.password_confirmation
      })
      
      console.log('Registration response:', response)
      
      // 登録と同時にログイン
      localStorage.setItem('auth-token', response.token)
      localStorage.setItem('auth-user', JSON.stringify(response.user))
      
      this.$axios.setToken(response.token, 'Bearer')
      
      commit('SET_USER', response.user)
      commit('SET_TOKEN', response.token)
      
      return { success: true, message: response.message }
    } catch (error) {
      console.error('Registration error:', error)
      console.error('Error response:', error.response)
      const message = error.response?.data?.message || '登録に失敗しました'
      return { success: false, error: message }
    } finally {
      commit('SET_LOADING', false)
    }
  },
  
  // API ログアウト
  async logout({ commit }) {
    try {
      const token = localStorage.getItem('auth-token')
      if (token) {
        this.$axios.setToken(token, 'Bearer')
        await this.$axios.$post('/logout')
      }
    } catch (error) {
      // ログアウトAPIが失敗してもローカルの認証情報はクリア
      console.warn('API logout failed:', error)
    } finally {
      localStorage.removeItem('auth-token')
      localStorage.removeItem('auth-user')
      this.$axios.setToken(false)
      
      commit('LOGOUT')
      return { success: true }
    }
  },
  
  // 認証状態の復元（ページリロード時）
  restoreAuth({ commit }) {
    const token = localStorage.getItem('auth-token')
    const user = localStorage.getItem('auth-user')
    
    if (token && user) {
      try {
        this.$axios.setToken(token, 'Bearer')
        commit('SET_TOKEN', token)
        commit('SET_USER', JSON.parse(user))
      } catch (error) {
        localStorage.removeItem('auth-token')
        localStorage.removeItem('auth-user')
        this.$axios.setToken(false)
      }
    }
  },
  
  // ユーザー情報を更新
  async fetchUser({ commit }) {
    try {
      const response = await this.$axios.$get('/user')
      commit('SET_USER', response.user)
      return response.user
    } catch (error) {
      // 認証エラーの場合はログアウト
      if (error.response?.status === 401) {
        commit('LOGOUT')
        localStorage.removeItem('auth-token')
        localStorage.removeItem('auth-user')
        this.$axios.setToken(false)
      }
      throw error
    }
  }
}