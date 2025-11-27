<template>
  <div class="staff-attendance">
    <div class="staff-attendance__page-header">
      <div class="staff-attendance__header-content">
        <button
          type="button"
          @click="$router.push(`/admin/staff/${staffId}`)"
          class="staff-attendance__back-btn"
        >
          <i class="fas fa-arrow-left"></i>
          スタッフ詳細に戻る
        </button>
        <h1 class="staff-attendance__title">{{ staffName }}さんの勤怠</h1>
      </div>
    </div>

    <div class="staff-attendance__container">
      <!-- 月選択 -->
      <div class="staff-attendance__month-selector">
        <button
          type="button"
          @click="previousMonth"
          class="staff-attendance__month-btn"
          aria-label="前月"
        >
          <i class="fas fa-chevron-left"></i>
          前月
        </button>

        <div class="staff-attendance__month-display">
          <input
            v-model="selectedMonth"
            type="month"
            class="staff-attendance__month-input"
            @change="handleMonthChange"
          />
          <span class="staff-attendance__month-label">{{ formatMonthLabel(selectedMonth) }}</span>
        </div>

        <button
          type="button"
          @click="nextMonth"
          class="staff-attendance__month-btn"
          :disabled="isCurrentMonth"
          aria-label="翌月"
        >
          翌月
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      <!-- 月次統計サマリー -->
      <div class="staff-attendance__stats">
        <div class="staff-attendance__stat-card">
          <div class="staff-attendance__stat-icon staff-attendance__stat-icon--days">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div class="staff-attendance__stat-info">
            <h3>{{ statistics.workDays }}</h3>
            <p>出勤日数</p>
          </div>
        </div>

        <div class="staff-attendance__stat-card">
          <div class="staff-attendance__stat-icon staff-attendance__stat-icon--hours">
            <i class="fas fa-clock"></i>
          </div>
          <div class="staff-attendance__stat-info">
            <h3>{{ statistics.totalHours }}</h3>
            <p>総勤務時間</p>
          </div>
        </div>

        <div class="staff-attendance__stat-card">
          <div class="staff-attendance__stat-icon staff-attendance__stat-icon--late">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="staff-attendance__stat-info">
            <h3>{{ statistics.lateCount }}</h3>
            <p>遅刻回数</p>
          </div>
        </div>

        <div class="staff-attendance__stat-card">
          <div class="staff-attendance__stat-icon staff-attendance__stat-icon--absence">
            <i class="fas fa-user-times"></i>
          </div>
          <div class="staff-attendance__stat-info">
            <h3>{{ statistics.absenceCount }}</h3>
            <p>欠勤日数</p>
          </div>
        </div>
      </div>

      <!-- 勤怠一覧テーブル -->
      <div class="staff-attendance__content">
        <div class="staff-attendance__header">
          <h2>勤怠記録</h2>
          <div class="staff-attendance__actions">
            <button
              type="button"
              @click="exportToCSV"
              class="staff-attendance__btn staff-attendance__btn--secondary"
            >
              <i class="fas fa-download"></i>
              CSV出力
            </button>
          </div>
        </div>

        <div v-if="isLoading" class="staff-attendance__loading">
          <LoadingSpinner />
          <p>勤怠データを読み込み中...</p>
        </div>

        <div v-else class="staff-attendance__table-container">
          <table class="staff-attendance__table">
            <thead>
              <tr>
                <th>日付</th>
                <th>出勤時刻</th>
                <th>退勤時刻</th>
                <th>休憩時間</th>
                <th>勤務時間</th>
                <th>ステータス</th>
                <th>備考</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="record in attendanceRecords"
                :key="record.date"
                class="staff-attendance__row"
                :class="getRowClass(record)"
              >
                <td class="staff-attendance__date-cell">
                  <div class="staff-attendance__date">
                    {{ formatDate(record.date) }}
                  </div>
                  <div class="staff-attendance__day-label">
                    {{ getDayLabel(record.date) }}
                  </div>
                </td>
                <td>
                  <span :class="getTimeClass(record.check_in_time, record.is_late)">
                    {{ formatTime(record.check_in_time) }}
                    <i v-if="record.is_late" class="fas fa-exclamation-triangle staff-attendance__late-icon"></i>
                  </span>
                </td>
                <td>{{ formatTime(record.check_out_time) }}</td>
                <td>{{ formatDuration(record.break_time) }}</td>
                <td>{{ formatDuration(record.work_time) }}</td>
                <td>
                  <StatusBadge :status="record.status" />
                </td>
                <td>
                  <span v-if="record.note" class="staff-attendance__note">
                    {{ record.note }}
                  </span>
                  <span v-else class="staff-attendance__no-note">−</span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- 空データ時の表示 -->
          <div v-if="attendanceRecords.length === 0" class="staff-attendance__empty">
            <i class="fas fa-calendar-times"></i>
            <p>{{ formatMonthLabel(selectedMonth) }}の勤怠記録はありません</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StaffAttendance',
  middleware: ['auth', 'verified', 'admin'],

  data() {
    return {
      staffId: null,
      staffName: '',
      selectedMonth: this.getCurrentMonthString(),
      attendanceRecords: [],
      statistics: {
        workDays: 0,
        totalHours: 0,
        lateCount: 0,
        absenceCount: 0
      },
      isLoading: false
    }
  },

  computed: {
    isCurrentMonth() {
      return this.selectedMonth === this.getCurrentMonthString()
    }
  },

  watch: {
    selectedMonth() {
      this.loadAttendanceData()
    }
  },

  async mounted() {
    this.staffId = this.$route.params.id
    await this.loadStaffInfo()
    await this.loadAttendanceData()
  },

  methods: {
    getCurrentMonthString() {
      const today = new Date()
      const year = today.getFullYear()
      const month = String(today.getMonth() + 1).padStart(2, '0')
      return `${year}-${month}`
    },

    async loadStaffInfo() {
      try {
        const response = await this.$axios.get(`/api/staff/${this.staffId}`, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })
        this.staffName = response.data.name
      } catch (error) {
        console.error('スタッフ情報取得エラー:', error)
        // ダミーデータ
        this.staffName = '山田太郎'
      }
    },

    async loadAttendanceData() {
      try {
        this.isLoading = true

        const response = await this.$axios.get(`/api/admin/staff/${this.staffId}/attendance/monthly`, {
          params: {
            month: this.selectedMonth
          },
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.attendanceRecords = response.data.records || []
        this.statistics = response.data.statistics || {
          workDays: 0,
          totalHours: 0,
          lateCount: 0,
          absenceCount: 0
        }
      } catch (error) {
        console.error('月次勤怠データ取得エラー:', error)

        // 開発中: ダミーデータ
        this.attendanceRecords = this.generateDummyData()
        this.calculateStatistics()

        if (this.$toast) {
          this.$toast.warning('デモデータを表示しています（API未実装）')
        }
      } finally {
        this.isLoading = false
      }
    },

    generateDummyData() {
      const records = []
      const [year, month] = this.selectedMonth.split('-')
      const daysInMonth = new Date(year, month, 0).getDate()

      for (let day = 1; day <= daysInMonth; day++) {
        const date = `${year}-${month}-${String(day).padStart(2, '0')}`
        const dayOfWeek = new Date(date).getDay()

        // 土日はスキップ
        if (dayOfWeek === 0 || dayOfWeek === 6) continue

        records.push({
          date,
          check_in_time: day % 5 === 0 ? '09:15:00' : '09:00:00',
          check_out_time: day % 3 === 0 ? null : '18:00:00',
          break_time: day % 3 === 0 ? 0 : 60,
          work_time: day % 3 === 0 ? 0 : 480,
          status: day % 3 === 0 ? 'working' : 'completed',
          is_late: day % 5 === 0,
          note: day % 7 === 0 ? '客先訪問' : null
        })
      }

      return records
    },

    calculateStatistics() {
      this.statistics.workDays = this.attendanceRecords.filter(r => r.check_in_time).length
      this.statistics.totalHours = Math.floor(
        this.attendanceRecords.reduce((sum, r) => sum + (r.work_time || 0), 0) / 60
      )
      this.statistics.lateCount = this.attendanceRecords.filter(r => r.is_late).length
      this.statistics.absenceCount = this.attendanceRecords.filter(r => !r.check_in_time).length
    },

    handleMonthChange() {
      // watch で loadAttendanceData が自動実行される
    },

    previousMonth() {
      const [year, month] = this.selectedMonth.split('-').map(Number)
      const date = new Date(year, month - 2, 1) // month - 1 で現在月、-2で前月
      const newYear = date.getFullYear()
      const newMonth = String(date.getMonth() + 1).padStart(2, '0')
      this.selectedMonth = `${newYear}-${newMonth}`
    },

    nextMonth() {
      if (!this.isCurrentMonth) {
        const [year, month] = this.selectedMonth.split('-').map(Number)
        const date = new Date(year, month, 1) // month で翌月
        const newYear = date.getFullYear()
        const newMonth = String(date.getMonth() + 1).padStart(2, '0')
        this.selectedMonth = `${newYear}-${newMonth}`
      }
    },

    formatMonthLabel(monthString) {
      if (!monthString) return ''
      const [year, month] = monthString.split('-')
      return `${year}年${parseInt(month)}月`
    },

    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return `${date.getMonth() + 1}/${date.getDate()}`
    },

    getDayLabel(dateString) {
      if (!dateString) return ''
      const days = ['日', '月', '火', '水', '木', '金', '土']
      const date = new Date(dateString)
      return days[date.getDay()]
    },

    formatTime(timeString) {
      if (!timeString) return '−'
      return timeString.substring(0, 5)
    },

    formatDuration(minutes) {
      if (!minutes || minutes === 0) return '−'
      const hours = Math.floor(minutes / 60)
      const mins = minutes % 60
      return `${hours}:${String(mins).padStart(2, '0')}`
    },

    getRowClass(record) {
      return {
        'staff-attendance__row--weekend': this.isWeekend(record.date),
        'staff-attendance__row--late': record.is_late
      }
    },

    getTimeClass(timeString, isLate) {
      if (!timeString) return ''
      return isLate ? 'staff-attendance__time--late' : ''
    },

    isWeekend(dateString) {
      const dayOfWeek = new Date(dateString).getDay()
      return dayOfWeek === 0 || dayOfWeek === 6
    },

    exportToCSV() {
      if (this.$toast) {
        this.$toast.info('CSV出力機能は開発中です')
      } else {
        alert('CSV出力機能は開発中です')
      }
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-staff-attendance.css" scoped></style>
