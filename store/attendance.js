// 勤怠管理用のVuexストア
export const state = () => ({
  currentStatus: 'not-working', // not-working, working, on-break, finished
  workStartTime: null,
  workEndTime: null,
  breakStartTime: null,
  breakEndTime: null,
  breakDuration: 0, // 合計休憩時間（分）
  todayAttendance: null
})

export const getters = {
  currentStatus: (state) => state.currentStatus,
  
  workStartTime: (state) => {
    return state.workStartTime ? formatTime(state.workStartTime) : null
  },
  
  workEndTime: (state) => {
    return state.workEndTime ? formatTime(state.workEndTime) : null
  },
  
  breakStartTime: (state) => {
    return state.breakStartTime ? formatTime(state.breakStartTime) : null
  },
  
  isWorking: (state) => {
    return state.currentStatus === 'working' || state.currentStatus === 'on-break'
  },
  
  totalWorkTime: (state) => {
    if (!state.workStartTime) return null
    
    const startTime = new Date(state.workStartTime)
    const endTime = state.workEndTime ? new Date(state.workEndTime) : new Date()
    
    // 勤務時間から休憩時間を差し引く
    const workMilliseconds = endTime - startTime - (state.breakDuration * 60 * 1000)
    
    if (workMilliseconds <= 0) return '00:00'
    
    const hours = Math.floor(workMilliseconds / (1000 * 60 * 60))
    const minutes = Math.floor((workMilliseconds % (1000 * 60 * 60)) / (1000 * 60))
    
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
  },
  
  todayAttendance: (state) => state.todayAttendance
}

export const mutations = {
  SET_CURRENT_STATUS(state, status) {
    state.currentStatus = status
  },
  
  SET_WORK_START_TIME(state, time) {
    state.workStartTime = time
  },
  
  SET_WORK_END_TIME(state, time) {
    state.workEndTime = time
  },
  
  SET_BREAK_START_TIME(state, time) {
    state.breakStartTime = time
  },
  
  SET_BREAK_END_TIME(state, time) {
    state.breakEndTime = time
  },
  
  ADD_BREAK_DURATION(state, minutes) {
    state.breakDuration += minutes
  },
  
  SET_BREAK_DURATION(state, minutes) {
    state.breakDuration = minutes
  },
  
  SET_TODAY_ATTENDANCE(state, attendance) {
    state.todayAttendance = attendance
    
    // 勤怠データから状態を復元
    if (attendance) {
      if (attendance.clock_out_time) {
        state.currentStatus = 'finished'
        state.workStartTime = attendance.clock_in_time
        state.workEndTime = attendance.clock_out_time
      } else if (attendance.break_start_time && !attendance.break_end_time) {
        state.currentStatus = 'on-break'
        state.workStartTime = attendance.clock_in_time
        state.breakStartTime = attendance.break_start_time
      } else if (attendance.clock_in_time) {
        state.currentStatus = 'working'
        state.workStartTime = attendance.clock_in_time
      }
      
      // 休憩時間の計算
      if (attendance.break_records) {
        state.breakDuration = attendance.break_records.reduce((total, record) => {
          if (record.start_time && record.end_time) {
            const start = new Date(record.start_time)
            const end = new Date(record.end_time)
            return total + Math.floor((end - start) / (1000 * 60))
          }
          return total
        }, 0)
      }
    }
  },
  
  RESET_ATTENDANCE(state) {
    state.currentStatus = 'not-working'
    state.workStartTime = null
    state.workEndTime = null
    state.breakStartTime = null
    state.breakEndTime = null
    state.breakDuration = 0
    state.todayAttendance = null
  }
}

export const actions = {
  // 勤怠状況を取得
  async fetchStatus({ commit }) {
    try {
      const response = await this.$axios.$get('/api/attendance/status')
      
      // APIレスポンスに基づいて状態を設定
      commit('SET_CURRENT_STATUS', response.status)
      
      if (response.attendance) {
        commit('SET_WORK_START_TIME', response.attendance.check_in)
        if (response.attendance.check_out) {
          commit('SET_WORK_END_TIME', response.attendance.check_out)
        }
        
        // 休憩時間の計算
        if (response.attendance.rests) {
          const totalBreakMinutes = response.attendance.rests.reduce((total, rest) => {
            if (rest.rest_start && rest.rest_end) {
              const start = new Date(`1970-01-01T${rest.rest_start}`)
              const end = new Date(`1970-01-01T${rest.rest_end}`)
              return total + Math.floor((end - start) / (1000 * 60))
            }
            return total
          }, 0)
          commit('SET_BREAK_DURATION', totalBreakMinutes)
        }
      }
      
      if (response.active_rest) {
        commit('SET_BREAK_START_TIME', response.active_rest.rest_start)
      }
      
      return response
    } catch (error) {
      console.error('Fetch status error:', error)
      throw error
    }
  },
  
  // 出勤打刻
  async clockIn({ commit }) {
    try {
      const response = await this.$axios.$post('/api/attendance/check-in')
      
      commit('SET_WORK_START_TIME', response.attendance.check_in)
      commit('SET_CURRENT_STATUS', 'checked_in')
      
      return { success: true, message: response.message }
    } catch (error) {
      const message = error.response?.data?.message || '出勤打刻に失敗しました'
      return { success: false, error: message }
    }
  },
  
  // 退勤打刻
  async clockOut({ commit, dispatch }) {
    try {
      const response = await this.$axios.$post('/api/attendance/check-out')
      
      commit('SET_WORK_END_TIME', response.attendance.check_out)
      commit('SET_CURRENT_STATUS', 'checked_out')
      
      return { success: true, message: response.message }
    } catch (error) {
      const message = error.response?.data?.message || '退勤打刻に失敗しました'
      return { success: false, error: message }
    }
  },
  
  // 休憩開始
  async startBreak({ commit }) {
    try {
      const response = await this.$axios.$post('/api/attendance/start-rest')
      
      commit('SET_BREAK_START_TIME', response.rest.rest_start)
      commit('SET_CURRENT_STATUS', 'on_break')
      
      return { success: true, message: response.message }
    } catch (error) {
      const message = error.response?.data?.message || '休憩開始に失敗しました'
      return { success: false, error: message }
    }
  },
  
  // 休憩終了
  async endBreak({ commit }) {
    try {
      const response = await this.$axios.$post('/api/attendance/end-rest')
      
      commit('SET_BREAK_END_TIME', response.rest.rest_end)
      commit('SET_CURRENT_STATUS', 'checked_in')
      
      return { success: true, message: response.message }
    } catch (error) {
      const message = error.response?.data?.message || '休憩終了に失敗しました'
      return { success: false, error: message }
    }
  },
  
  // 勤怠状態リセット（日付変更時など）
  resetAttendance({ commit }) {
    commit('RESET_ATTENDANCE')
  }
}

// ヘルパー関数
function formatTime(timestamp) {
  const date = new Date(timestamp)
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  return `${hours}:${minutes}`
}