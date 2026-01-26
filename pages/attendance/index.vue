<template>
  <div class="attendance-index__container">
    <div class="attendance-index__page-header">
      <h1 class="attendance-index__title">
        勤怠一覧
      </h1>
      <p class="attendance-index__subtitle">
        過去の勤怠記録を確認できます
      </p>
    </div>

    <!-- フィルター -->
    <div class="attendance-index__filter-section">
      <h2 class="attendance-index__filter-title">
        絞り込み
      </h2>
      <div class="attendance-index__filter-grid">
        <div class="form-input">
          <label for="month-filter" class="form-input__label">年月</label>
          <input
            id="month-filter"
            v-model="selectedMonth"
            type="month"
            class="form-input__field"
          >
        </div>
        <div class="attendance-index__search-container">
          <button
            :disabled="isLoading"
            class="attendance-index__search-btn"
            @click="loadAttendanceData(true)"
          >
            <i class="fas fa-search mr-2" />
            検索
          </button>
        </div>
      </div>
    </div>

    <!-- 勤怠記録テーブル -->
    <AttendanceTable
      :title="`${formatMonthYear(selectedMonth)}の勤怠記録`"
      :data="attendanceRecords"
      :columns="tableColumns"
      :loading="isLoading"
      :current-page="currentPage"
      :total-pages="totalPages"
      :total-items="totalItems"
      @page-change="handlePageChange"
    >
      <template #cell-date="{ item }">
        <div>
          <div class="attendance-index__date-primary">
            {{ formatDate(item.date) }}
          </div>
          <div class="attendance-index__date-secondary">
            {{ getDayOfWeek(item.date) }}
          </div>
        </div>
      </template>

      <template #actions="{ item }">
        <button
          v-if="canRequestCorrection(item)"
          class="attendance-index__action-btn attendance-index__action-btn--correction"
          @click="requestCorrection(item.id)"
        >
          <i class="fas fa-edit" />
          修正申請
        </button>
      </template>
    </AttendanceTable>
  </div>
</template>

<script>
import AttendanceTable from '~/components/AttendanceTable.vue'
import FormInput from '~/components/FormInput.vue'

export default {
  name: 'AttendanceList',
  components: {
    AttendanceTable,
    FormInput
  },
  middleware: ['auth', 'verified'],

  data () {
    return {
      selectedMonth: new Date().toISOString().slice(0, 7), // YYYY-MM形式
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      isLoading: false,
      attendanceRecords: []
    }
  },

  computed: {
    tableColumns () {
      return [
        { key: 'date', label: '日付', type: 'date' },
        { key: 'clockIn', label: '出勤時刻', type: 'time' },
        { key: 'clockOut', label: '退勤時刻', type: 'time' },
        { key: 'workHours', label: '労働時間', type: 'time' },
        { key: 'breakTime', label: '休憩時間', type: 'time' }
      ]
    }
  },

  mounted () {
    this.loadAttendanceData()
  },

  methods: {
    async loadAttendanceData (resetPage = false) {
      try {
        this.isLoading = true

        // 検索ボタンを押したときはページを1にリセット
        if (resetPage) {
          this.currentPage = 1
        }

        // 実際のAPI呼び出しで勤怠データを取得
        const params = {
          page: this.currentPage
        }

        // 選択された月でフィルタリング
        if (this.selectedMonth) {
          const [year, month] = this.selectedMonth.split('-')
          params.year = year
          params.month = month
        }

        const response = await this.$axios.$get('/api/attendance', { params })

        // Laravel pagination形式のレスポンスを処理
        this.attendanceRecords = response.data.map(record => ({
          id: record.id,
          date: record.date,
          clockIn: this.formatTimeSimple(record.check_in),
          clockOut: this.formatTimeSimple(record.check_out),
          workHours: this.calculateWorkHours(record),
          breakTime: this.calculateBreakTime(record),
          rests: record.rests || [],
          status: this.determineStatus(record)
        }))

        this.totalItems = response.total || response.data.length
        this.totalPages = response.last_page || Math.ceil(this.totalItems / 15)
      } catch (error) {
        console.error('勤怠データ取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('勤怠データの取得に失敗しました')
        }
      } finally {
        this.isLoading = false
      }
    },

    formatDate (dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        month: 'numeric',
        day: 'numeric'
      })
    },

    formatTimeSimple (timeString) {
      if (!timeString) { return null }
      // HH:MM:SS形式から HH:MM形式に変換
      return timeString.substring(0, 5)
    },

    formatMonthYear (monthString) {
      const date = new Date(monthString + '-01')
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long'
      })
    },

    getDayOfWeek (dateString) {
      const date = new Date(dateString)
      const days = ['日', '月', '火', '水', '木', '金', '土']
      return days[date.getDay()]
    },

    canRequestCorrection (record) {
      // 修正申請可能な条件をチェック
      return record.status !== 'pending' && record.clockIn
    },

    requestCorrection (recordId) {
      // 該当の勤怠記録を取得
      const record = this.attendanceRecords.find(r => r.id === recordId)
      if (!record) {
        console.error('Record not found:', recordId)
        return
      }

      // 日付を YYYY-MM-DD 形式に変換
      const dateStr = record.date.split('T')[0]

      // 修正申請ページへ日付付きで遷移
      this.$router.push({
        path: '/correction-requests/new',
        query: { date: dateStr }
      })
    },

    handlePageChange (page) {
      this.currentPage = page
      this.loadAttendanceData()
    },

    calculateWorkHours (record) {
      if (!record.check_in || !record.check_out) {
        return null
      }

      const dateStr = record.date.split('T')[0]
      const checkInDateTime = `${dateStr}T${record.check_in}`
      const checkOutDateTime = `${dateStr}T${record.check_out}`

      const checkIn = new Date(checkInDateTime)
      const checkOut = new Date(checkOutDateTime)

      if (isNaN(checkIn.getTime()) || isNaN(checkOut.getTime())) {
        return '0:00'
      }

      let workMilliseconds = checkOut - checkIn

      // 休憩時間を差し引く
      if (record.rests && record.rests.length > 0) {
        const totalBreakMilliseconds = record.rests.reduce((total, rest) => {
          if (rest.rest_start && rest.rest_end) {
            const restStartDateTime = `${dateStr}T${rest.rest_start}`
            const restEndDateTime = `${dateStr}T${rest.rest_end}`
            const restStart = new Date(restStartDateTime)
            const restEnd = new Date(restEndDateTime)

            if (!isNaN(restStart.getTime()) && !isNaN(restEnd.getTime())) {
              return total + (restEnd - restStart)
            }
          }
          return total
        }, 0)

        workMilliseconds -= totalBreakMilliseconds
      }

      if (workMilliseconds <= 0) { return '0:00' }

      const hours = Math.floor(workMilliseconds / (1000 * 60 * 60))
      const minutes = Math.floor(
        (workMilliseconds % (1000 * 60 * 60)) / (1000 * 60)
      )

      return `${hours}:${String(minutes).padStart(2, '0')}`
    },

    calculateBreakTime (record) {
      if (!record.rests || record.rests.length === 0) {
        return '0:00'
      }

      const dateStr = record.date.split('T')[0]

      const totalBreakMilliseconds = record.rests.reduce((total, rest) => {
        if (rest.rest_start && rest.rest_end) {
          const restStartDateTime = `${dateStr}T${rest.rest_start}`
          const restEndDateTime = `${dateStr}T${rest.rest_end}`
          const restStart = new Date(restStartDateTime)
          const restEnd = new Date(restEndDateTime)

          if (!isNaN(restStart.getTime()) && !isNaN(restEnd.getTime())) {
            return total + (restEnd - restStart)
          }
        }
        return total
      }, 0)

      if (totalBreakMilliseconds <= 0) { return '0:00' }

      const hours = Math.floor(totalBreakMilliseconds / (1000 * 60 * 60))
      const minutes = Math.floor(
        (totalBreakMilliseconds % (1000 * 60 * 60)) / (1000 * 60)
      )

      return `${hours}:${String(minutes).padStart(2, '0')}`
    },

    determineStatus (record) {
      if (!record.check_in) { return 'absent' }
      if (!record.check_out) { return 'working' }
      return 'completed'
    }
  }
}
</script>

<style scoped>
@import "@/assets/css/pages/attendance-index.css";
</style>
