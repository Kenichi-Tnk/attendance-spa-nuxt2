<template>
  <div class="staff-attendance">
    <div class="staff-attendance__page-header">
      <div class="staff-attendance__header-content">
        <button
          type="button"
          @click="$router.push('/admin/staff')"
          class="staff-attendance__back-btn"
        >
          <i class="fas fa-arrow-left"></i>
          スタッフ一覧に戻る
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
                <th>詳細</th>
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
                  <span :class="getTimeClass(record.check_in, record.status === '遅刻')">
                    {{ formatTime(record.check_in) }}
                    <i v-if="record.status === '遅刻'" class="fas fa-exclamation-triangle staff-attendance__late-icon"></i>
                  </span>
                </td>
                <td>{{ formatTime(record.check_out) }}</td>
                <td>{{ formatHours(record.rest_time) }}</td>
                <td>{{ formatHours(record.work_time) }}</td>
                <td>
                  <button 
                    v-if="record.check_in"
                    @click="openDetailModal(record)"
                    class="staff-attendance__detail-btn"
                  >
                    <i class="fas fa-edit"></i> 詳細
                  </button>
                  <span v-else class="staff-attendance__no-data">−</span>
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

      <!-- 詳細編集モーダル -->
      <div v-if="showDetailModal" class="modal-overlay" @click="closeDetailModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>勤怠詳細 - {{ formatDate(editingRecord.date) }}({{ getDayLabel(editingRecord.date) }})</h3>
            <button @click="closeDetailModal" class="modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="saveAttendanceDetail">
              <div class="form-group">
                <label>出勤時刻</label>
                <input 
                  v-model="editingRecord.check_in" 
                  type="time" 
                  class="form-control"
                  required
                />
              </div>

              <div class="form-group">
                <label>退勤時刻</label>
                <input 
                  v-model="editingRecord.check_out" 
                  type="time" 
                  class="form-control"
                />
              </div>

              <div class="form-section">
                <h4>休憩時間</h4>
                <div 
                  v-for="(rest, index) in editingRecord.rests" 
                  :key="index"
                  class="rest-item"
                >
                  <div class="rest-inputs">
                    <div class="form-group">
                      <label>開始</label>
                      <input 
                        v-model="rest.rest_start" 
                        type="time" 
                        class="form-control"
                      />
                    </div>
                    <div class="form-group">
                      <label>終了</label>
                      <input 
                        v-model="rest.rest_end" 
                        type="time" 
                        class="form-control"
                      />
                    </div>
                    <button 
                      type="button"
                      @click="removeRest(index)"
                      class="btn-remove-rest"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
                <button 
                  type="button"
                  @click="addRest"
                  class="btn-add-rest"
                >
                  <i class="fas fa-plus"></i> 休憩時間を追加
                </button>
              </div>

              <div class="modal-actions">
                <button type="button" @click="closeDetailModal" class="btn-cancel">
                  キャンセル
                </button>
                <button type="submit" class="btn-save">
                  <i class="fas fa-save"></i> 保存
                </button>
              </div>
            </form>
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
      isLoading: false,
      showDetailModal: false,
      editingRecord: {
        id: null,
        date: '',
        check_in: '',
        check_out: '',
        rests: []
      }
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
        // スタッフ一覧から該当スタッフを検索
        const response = await this.$axios.get('/api/staff', {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })
        
        // ページネーション対応: data配列から該当スタッフを検索
        const staff = response.data.data.find(s => s.id === parseInt(this.staffId))
        
        if (staff) {
          this.staffName = staff.name
        } else {
          console.warn(`スタッフID ${this.staffId} が見つかりませんでした`)
          this.staffName = 'スタッフ情報取得中...'
        }
      } catch (error) {
        console.error('スタッフ情報取得エラー:', error)
        // エラー時はダミーデータ
        this.staffName = 'スタッフ'
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
        // API未実装時はダミーデータを表示
        this.attendanceRecords = this.generateDummyData()
        this.calculateStatistics()
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
      this.statistics.workDays = this.attendanceRecords.filter(r => r.check_in).length
      this.statistics.totalHours = Math.floor(
        this.attendanceRecords.reduce((sum, r) => sum + (r.work_time || 0), 0) / 60
      )
      this.statistics.lateCount = this.attendanceRecords.filter(r => r.is_late).length
      this.statistics.absenceCount = this.attendanceRecords.filter(r => !r.check_in).length
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

    formatHours(hours) {
      if (!hours || hours === 0) return '−'
      const h = Math.floor(hours)
      const m = Math.round((hours - h) * 60)
      return `${h}:${String(m).padStart(2, '0')}`
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
      // 赤字表示を無効化
      return ''
    },

    isWeekend(dateString) {
      const dayOfWeek = new Date(dateString).getDay()
      return dayOfWeek === 0 || dayOfWeek === 6
    },

    exportToCSV() {
      if (this.attendanceRecords.length === 0) {
        if (this.$toast) {
          this.$toast.warning('出力するデータがありません')
        } else {
          alert('出力するデータがありません')
        }
        return
      }

      // CSVヘッダー
      const headers = ['日付', '曜日', '出勤時刻', '退勤時刻', '休憩時間', '勤務時間']
      
      // CSVデータ作成
      const rows = this.attendanceRecords.map(record => {
        const date = this.formatDate(record.date)
        const day = this.getDayLabel(record.date)
        const checkIn = record.check_in || '−'
        const checkOut = record.check_out || '−'
        const restTime = this.formatHours(record.rest_time)
        const workTime = this.formatHours(record.work_time)
        
        return [date, day, checkIn, checkOut, restTime, workTime]
      })

      // CSV文字列作成（ダブルクォートでエスケープ）
      const escapeCsv = (value) => {
        const str = String(value)
        if (str.includes(',') || str.includes('"') || str.includes('\n')) {
          return `"${str.replace(/"/g, '""')}"`
        }
        return str
      }

      const csvContent = [
        headers.map(escapeCsv).join(','),
        ...rows.map(row => row.map(escapeCsv).join(','))
      ].join('\r\n')

      // BOMを追加（Excelで文字化けしないように）
      const bom = '\uFEFF'
      const blob = new Blob([bom + csvContent], { type: 'text/csv;charset=utf-8;' })
      
      // ファイル名生成（スタッフ名_年月.csv）
      const monthStr = this.selectedMonth.replace('-', '年') + '月'
      const filename = `${this.staffName}_${monthStr}_勤怠データ.csv`
      
      // ダウンロード
      const link = document.createElement('a')
      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob)
        link.setAttribute('href', url)
        link.setAttribute('download', filename)
        link.style.visibility = 'hidden'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        URL.revokeObjectURL(url)
        
        if (this.$toast) {
          this.$toast.success('CSVファイルをダウンロードしました')
        }
      }
    },

    async openDetailModal(record) {
      // 詳細データを取得
      try {
        const response = await this.$axios.get(`/api/attendance/${record.id}`, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.editingRecord = {
          id: response.data.id,
          date: response.data.date,
          check_in: this.formatTimeForInput(response.data.check_in),
          check_out: this.formatTimeForInput(response.data.check_out),
          rests: response.data.rests.map(rest => ({
            id: rest.id,
            rest_start: this.formatTimeForInput(rest.rest_start),
            rest_end: this.formatTimeForInput(rest.rest_end)
          }))
        }

        this.showDetailModal = true
      } catch (error) {
        console.error('詳細データ取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('詳細データの取得に失敗しました')
        }
      }
    },

    closeDetailModal() {
      this.showDetailModal = false
      this.editingRecord = {
        id: null,
        date: '',
        check_in: '',
        check_out: '',
        rests: []
      }
    },

    addRest() {
      this.editingRecord.rests.push({
        id: null,
        rest_start: '',
        rest_end: ''
      })
    },

    removeRest(index) {
      this.editingRecord.rests.splice(index, 1)
    },

    async saveAttendanceDetail() {
      try {
        await this.$axios.put(`/api/admin/attendance/${this.editingRecord.id}`, {
          check_in: this.editingRecord.check_in,
          check_out: this.editingRecord.check_out,
          rests: this.editingRecord.rests
        }, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        if (this.$toast) {
          this.$toast.success('勤怠情報を更新しました')
        }

        this.closeDetailModal()
        await this.loadAttendanceData()
      } catch (error) {
        console.error('勤怠更新エラー:', error)
        if (this.$toast) {
          this.$toast.error('勤怠情報の更新に失敗しました')
        }
      }
    },

    formatTimeForInput(timeString) {
      if (!timeString) return ''
      // "HH:mm" or "HH:mm:ss" format to "HH:mm"
      return timeString.substring(0, 5)
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-staff-attendance.css" scoped></style>
