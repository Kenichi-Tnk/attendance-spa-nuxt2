<template>
  <div class="admin-daily-attendance">
    <div class="admin-daily-attendance__page-header">
      <h1 class="admin-daily-attendance__title">日次勤怠一覧</h1>
      <p class="admin-daily-attendance__subtitle">全スタッフの日毎の勤怠状況を確認</p>
    </div>

    <div class="admin-daily-attendance__container">
      <!-- 日付選択 -->
      <div class="admin-daily-attendance__date-selector">
        <button
          type="button"
          @click="previousDate"
          class="admin-daily-attendance__date-btn"
          aria-label="前の日"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="admin-daily-attendance__date-display">
          <input
            v-model="selectedDate"
            type="date"
            class="admin-daily-attendance__date-input"
            @change="handleDateChange"
          />
          <span class="admin-daily-attendance__date-label">{{ formatDateLabel(selectedDate) }}</span>
        </div>
        
        <button
          type="button"
          @click="nextDate"
          class="admin-daily-attendance__date-btn"
          :disabled="isToday"
          aria-label="次の日"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
        
        <button
          type="button"
          @click="goToToday"
          class="admin-daily-attendance__today-btn"
          :disabled="isToday"
        >
          <i class="fas fa-calendar-day"></i>
          今日
        </button>
      </div>

      <!-- 統計サマリー -->
      <div class="admin-daily-attendance__stats">
        <div class="admin-daily-attendance__stat-card">
          <div class="admin-daily-attendance__stat-icon admin-daily-attendance__stat-icon--total">
            <i class="fas fa-users"></i>
          </div>
          <div class="admin-daily-attendance__stat-info">
            <h3>{{ statistics.total }}</h3>
            <p>総スタッフ数</p>
          </div>
        </div>

        <div class="admin-daily-attendance__stat-card">
          <div class="admin-daily-attendance__stat-icon admin-daily-attendance__stat-icon--present">
            <i class="fas fa-user-check"></i>
          </div>
          <div class="admin-daily-attendance__stat-info">
            <h3>{{ statistics.present }}</h3>
            <p>出勤中</p>
          </div>
        </div>

        <div class="admin-daily-attendance__stat-card">
          <div class="admin-daily-attendance__stat-icon admin-daily-attendance__stat-icon--completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="admin-daily-attendance__stat-info">
            <h3>{{ statistics.completed }}</h3>
            <p>退勤済み</p>
          </div>
        </div>

        <div class="admin-daily-attendance__stat-card">
          <div class="admin-daily-attendance__stat-icon admin-daily-attendance__stat-icon--absent">
            <i class="fas fa-user-times"></i>
          </div>
          <div class="admin-daily-attendance__stat-info">
            <h3>{{ statistics.absent }}</h3>
            <p>未出勤</p>
          </div>
        </div>
      </div>

      <!-- 勤怠一覧テーブル -->
      <div class="admin-daily-attendance__content">
        <div class="admin-daily-attendance__header">
          <h2>勤怠記録</h2>
          <div class="admin-daily-attendance__actions">
            <button
              type="button"
              @click="exportToCSV"
              class="admin-daily-attendance__btn admin-daily-attendance__btn--secondary"
            >
              <i class="fas fa-download"></i>
              CSV出力
            </button>
          </div>
        </div>

        <div v-if="isLoading" class="admin-daily-attendance__loading">
          <LoadingSpinner />
          <p>勤怠データを読み込み中...</p>
        </div>

        <div v-else class="admin-daily-attendance__table-container">
          <table class="admin-daily-attendance__table">
            <thead>
              <tr>
                <th>スタッフ</th>
                <th>出勤時刻</th>
                <th>退勤時刻</th>
                <th>休憩時間</th>
                <th>勤務時間</th>
                <th>ステータス</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="record in attendanceRecords"
                :key="record.user_id"
                class="admin-daily-attendance__row"
              >
                <td class="admin-daily-attendance__staff-info">
                  <div class="admin-daily-attendance__avatar">
                    <i class="fas fa-user"></i>
                  </div>
                  <div>
                    <div class="admin-daily-attendance__name">{{ record.user_name }}</div>
                    <div class="admin-daily-attendance__id">ID: {{ record.user_id }}</div>
                  </div>
                </td>
                <td>
                  <span :class="getTimeClass(record.check_in_time)">
                    {{ formatTime(record.check_in_time) }}
                  </span>
                </td>
                <td>
                  <span :class="getTimeClass(record.check_out_time)">
                    {{ formatTime(record.check_out_time) }}
                  </span>
                </td>
                <td>{{ formatDuration(record.break_time) }}</td>
                <td>{{ formatDuration(record.work_time) }}</td>
                <td>
                  <StatusBadge :status="record.status" />
                </td>
                <td class="admin-daily-attendance__actions-cell">
                  <button
                    type="button"
                    class="admin-daily-attendance__action-btn admin-daily-attendance__action-btn--detail"
                    @click="viewDetail(record)"
                    :aria-label="`詳細 ${record.user_name}`"
                    title="詳細"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- 空データ時の表示 -->
          <div v-if="attendanceRecords.length === 0" class="admin-daily-attendance__empty">
            <i class="fas fa-calendar-times"></i>
            <p>{{ formatDateLabel(selectedDate) }}の勤怠記録はありません</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminDailyAttendance',
  middleware: ['auth', 'verified', 'admin'],

  data() {
    return {
      selectedDate: this.getTodayString(),
      attendanceRecords: [],
      statistics: {
        total: 0,
        present: 0,
        completed: 0,
        absent: 0
      },
      isLoading: false
    }
  },

  computed: {
    isToday() {
      return this.selectedDate === this.getTodayString()
    }
  },

  watch: {
    selectedDate() {
      this.loadAttendanceData()
    }
  },

  mounted() {
    this.loadAttendanceData()
  },

  methods: {
    getTodayString() {
      const today = new Date()
      return today.toISOString().split('T')[0]
    },

    async loadAttendanceData() {
      try {
        this.isLoading = true

        const response = await this.$axios.get('/api/admin/attendance/daily', {
          params: {
            date: this.selectedDate
          },
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.attendanceRecords = response.data.records || []
        this.statistics = response.data.statistics || {
          total: 0,
          present: 0,
          completed: 0,
          absent: 0
        }
      } catch (error) {
        console.error('日次勤怠データ取得エラー:', error)
        
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
      // 開発中のダミーデータ
      return [
        {
          user_id: 1,
          user_name: '山田太郎',
          check_in_time: '09:00:00',
          check_out_time: '18:00:00',
          break_time: 60,
          work_time: 480,
          status: 'completed'
        },
        {
          user_id: 2,
          user_name: '佐藤花子',
          check_in_time: '09:15:00',
          check_out_time: null,
          break_time: 0,
          work_time: 0,
          status: 'working'
        },
        {
          user_id: 3,
          user_name: '鈴木一郎',
          check_in_time: null,
          check_out_time: null,
          break_time: 0,
          work_time: 0,
          status: 'absent'
        }
      ]
    },

    calculateStatistics() {
      this.statistics.total = this.attendanceRecords.length
      this.statistics.present = this.attendanceRecords.filter(r => r.status === 'working').length
      this.statistics.completed = this.attendanceRecords.filter(r => r.status === 'completed').length
      this.statistics.absent = this.attendanceRecords.filter(r => r.status === 'absent').length
    },

    handleDateChange() {
      // 日付入力変更時は loadAttendanceData が watch で自動実行される
    },

    previousDate() {
      const date = new Date(this.selectedDate)
      date.setDate(date.getDate() - 1)
      this.selectedDate = date.toISOString().split('T')[0]
    },

    nextDate() {
      if (!this.isToday) {
        const date = new Date(this.selectedDate)
        date.setDate(date.getDate() + 1)
        this.selectedDate = date.toISOString().split('T')[0]
      }
    },

    goToToday() {
      this.selectedDate = this.getTodayString()
    },

    formatDateLabel(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      const days = ['日', '月', '火', '水', '木', '金', '土']
      return `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日（${days[date.getDay()]}）`
    },

    formatTime(timeString) {
      if (!timeString) return '−'
      return timeString.substring(0, 5)
    },

    formatDuration(minutes) {
      if (!minutes || minutes === 0) return '−'
      const hours = Math.floor(minutes / 60)
      const mins = minutes % 60
      return `${hours}時間${mins}分`
    },

    getTimeClass(timeString) {
      if (!timeString) return ''
      // 遅刻判定などのクラスを返す（必要に応じて拡張）
      return ''
    },

    viewDetail(record) {
      // 詳細表示（モーダルまたは別ページ）
      if (this.$toast) {
        this.$toast.info(`${record.user_name}さんの詳細表示機能は開発中です`)
      } else {
        alert(`${record.user_name}さんの詳細表示機能は開発中です`)
      }
    },

    exportToCSV() {
      // CSV出力機能
      if (this.$toast) {
        this.$toast.info('CSV出力機能は開発中です')
      } else {
        alert('CSV出力機能は開発中です')
      }
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-daily-attendance.css" scoped></style>
