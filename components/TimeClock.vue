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
        休憩終了
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
      return this.$store.getters['attendance/currentStatus']
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
        'checked_in': '勤務中',
        'on_break': '休憩中',
        'checked_out': '退勤済'
      }
      return statusMap[this.attendanceStatus] || '勤務外'
    },
    statusClass() {
      return `time-clock__status-badge--${this.attendanceStatus}`
    },
    canClockIn() {
      return this.attendanceStatus === 'not_started'
    },
    canClockOut() {
      return this.attendanceStatus === 'checked_in' || this.attendanceStatus === 'on_break'
    },
    canBreakStart() {
      return this.attendanceStatus === 'checked_in'
    },
    canBreakEnd() {
      return this.attendanceStatus === 'on_break'
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
        if (result.success) {
          this.$toast.success(result.message)
        } else {
          this.$toast.error(result.error)
        }
      } catch (error) {
        this.$toast.error('出勤打刻に失敗しました')
        console.error('Clock in error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async clockOut() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/clockOut')
        if (result.success) {
          this.$toast.success(result.message)
        } else {
          this.$toast.error(result.error)
        }
      } catch (error) {
        this.$toast.error('退勤打刻に失敗しました')
        console.error('Clock out error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async startBreak() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/startBreak')
        if (result.success) {
          this.$toast.success(result.message)
        } else {
          this.$toast.error(result.error)
        }
      } catch (error) {
        this.$toast.error('休憩開始に失敗しました')
        console.error('Start break error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async endBreak() {
      this.isLoading = true
      try {
        const result = await this.$store.dispatch('attendance/endBreak')
        if (result.success) {
          this.$toast.success(result.message)
        } else {
          this.$toast.error(result.error)
        }
      } catch (error) {
        this.$toast.error('休憩終了に失敗しました')
        console.error('End break error:', error)
      } finally {
        this.isLoading = false
      }
    },
    async fetchAttendanceStatus() {
      try {
        await this.$store.dispatch('attendance/fetchStatus')
      } catch (error) {
        console.error('Fetch attendance status error:', error)
        // エラーが発生してもアプリの動作は継続
      }
    }
  }
}
</script>