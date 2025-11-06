<template>
  <div class="time-clock">
    <!-- 現在日時表示 -->
    <div class="time-clock__datetime">
      <div class="time-clock__date">{{ currentDate }}</div>
      <div class="time-clock__time">{{ currentTime }}</div>
    </div>

    <!-- 現在のステータス表示 -->
    <div class="time-clock__status">
      <div class="time-clock__status-badge" :class="statusClass">
        {{ currentStatusText }}
      </div>
    </div>

    <!-- 打刻ボタン -->
    <div class="time-clock__buttons">
      <button
        v-if="canClockIn"
        @click="clockIn"
        :disabled="isLoading"
        class="time-clock__btn time-clock__btn--clock-in"
      >
        <i class="fas fa-sign-in-alt"></i>
        出勤
      </button>

      <button
        v-if="canClockOut"
        @click="clockOut"
        :disabled="isLoading"
        class="time-clock__btn time-clock__btn--clock-out"
      >
        <i class="fas fa-sign-out-alt"></i>
        退勤
      </button>

      <button
        v-if="canBreakStart"
        @click="startBreak"
        :disabled="isLoading"
        class="time-clock__btn time-clock__btn--break"
      >
        <i class="fas fa-coffee"></i>
        休憩
      </button>

      <button
        v-if="canBreakEnd"
        @click="endBreak"
        :disabled="isLoading"
        class="time-clock__btn time-clock__btn--break-end"
      >
        <i class="fas fa-play"></i>
        休憩戻
      </button>
    </div>

    <!-- 勤務時間情報 -->
    <div v-if="workStartTime" class="time-clock__work-info">
      <div class="time-clock__work-start">
        出勤時刻: {{ workStartTime }}
      </div>
      <div v-if="totalWorkTime" class="time-clock__work-duration">
        勤務時間: {{ totalWorkTime }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TimeClock',
  data() {
    return {
      currentTime: '',
      currentDate: '',
      timeInterval: null,
      isLoading: false
    }
  },
  computed: {
    attendanceStatus() {
      const status = this.$store.state.attendance.currentStatus
      console.log('Current attendance status:', status)
      return status
    },
    workStartTime() {
      return this.$store.getters['attendance/workStartTime']
    },
    breakStartTime() {
      return this.$store.getters['attendance/breakStartTime']
    },
    totalWorkTime() {
      return this.$store.getters['attendance/totalWorkTime']
    },
    currentStatusText() {
      const statusMap = {
        'not_started': '勤務外',
        'checked_in': '出勤中', 
        'working': '出勤中',
        'on_break': '休憩中',
        'on-break': '休憩中',
        'checked_out': '退勤済',
        'finished': '退勤済'
      }
      return statusMap[this.attendanceStatus] || '勤務外'
    },
    statusClass() {
      return `time-clock__status-badge--${this.attendanceStatus}`
    },
    canClockIn() {
      // 勤務外か、退勤済み（但し同日の場合は不可）の場合のみ出勤可能
      return this.attendanceStatus === 'not_started' || 
             this.attendanceStatus === 'finished' ||
             !this.attendanceStatus
      // 注意: checked_outは同日再出勤を防ぐため削除
    },
    canClockOut() {
      // 出勤中（休憩中ではない）のみ退勤可能
      return this.attendanceStatus === 'checked_in' || this.attendanceStatus === 'working'
    },
    canBreakStart() {
      // 出勤中（休憩中ではない）のみ休憩開始可能
      return this.attendanceStatus === 'checked_in' || this.attendanceStatus === 'working'
    },
    canBreakEnd() {
      // 休憩中のみ休憩終了可能
      return this.attendanceStatus === 'on_break' || this.attendanceStatus === 'on-break'
    }
  },
  mounted() {
    this.updateDateTime()
    this.timeInterval = setInterval(this.updateDateTime, 1000)
    
    // 勤怠状況を取得
    this.fetchAttendanceStatus()
  },
  beforeDestroy() {
    if (this.timeInterval) {
      clearInterval(this.timeInterval)
    }
  },
  methods: {
    updateDateTime() {
      const now = new Date()
      
      // 日付フォーマット: 2023年6月1日(木)
      const year = now.getFullYear()
      const month = now.getMonth() + 1
      const day = now.getDate()
      const weekdays = ['日', '月', '火', '水', '木', '金', '土']
      const weekday = weekdays[now.getDay()]
      this.currentDate = `${year}年${month}月${day}日(${weekday})`
      
      // 時刻フォーマット: 08:00
      const hours = String(now.getHours()).padStart(2, '0')
      const minutes = String(now.getMinutes()).padStart(2, '0')
      this.currentTime = `${hours}:${minutes}`
    },
    async clockIn() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/clockIn')
        if (result && result.success) {
          // 成功時の処理
          await this.fetchAttendanceStatus()
        } else {
          console.error('Clock in failed:', result?.error)
        }
      } catch (error) {
        console.error('Clock in error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async clockOut() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/clockOut')
        if (result && result.success) {
          // 成功時の処理
          await this.fetchAttendanceStatus()
        } else {
          console.error('Clock out failed:', result?.error)
        }
      } catch (error) {
        console.error('Clock out error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async startBreak() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/startBreak')
        if (result && result.success) {
          // 成功時の処理
          await this.fetchAttendanceStatus()
        } else {
          console.error('Start break failed:', result?.error)
        }
      } catch (error) {
        console.error('Start break error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async endBreak() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/endBreak')
        if (result && result.success) {
          // 成功時の処理
          await this.fetchAttendanceStatus()
        } else {
          console.error('End break failed:', result?.error)
        }
      } catch (error) {
        console.error('End break error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async fetchAttendanceStatus() {
      try {
        console.log('Component: fetchAttendanceStatus called')
        const result = await this.$store.dispatch('attendance/fetchStatus')
        console.log('Component: fetchStatus result:', result)
        console.log('Component: current status after fetch:', this.attendanceStatus)
      } catch (error) {
        console.error('Fetch attendance status error:', error)
        // エラーが発生してもアプリの動作は継続
      }
    }
  }
}
</script>