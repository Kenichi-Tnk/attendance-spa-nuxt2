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
          <span>前日</span>
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
          <span>翌日</span>
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
                    <span>詳細</span>
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

    <!-- 詳細モーダル -->
    <div v-if="showDetailModal" class="modal-overlay" @click="closeDetailModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>勤怠詳細 - {{ detailRecord.user_name }}さん ({{ formatDateLabel(selectedDate) }})</h3>
          <button @click="closeDetailModal" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <!-- 勤務外の場合 -->
          <div v-if="detailRecord.status === 'absent'" class="detail-info">
            <p class="no-attendance-message">
              <i class="fas fa-info-circle"></i>
              この日は出勤記録がありません
            </p>
          </div>

          <!-- 勤務中の場合（閲覧のみ） -->
          <div v-else-if="detailRecord.status === 'working'" class="detail-info">
            <div class="info-alert">
              <i class="fas fa-user-clock"></i>
              現在勤務中のため、閲覧のみ可能です
            </div>

            <div class="form-group">
              <label>出勤時刻</label>
              <input 
                :value="detailRecord.check_in_time" 
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-group">
              <label>退勤時刻</label>
              <input 
                value="勤務中"
                type="text" 
                class="form-control"
                readonly
              />
            </div>

            <div class="form-section" v-if="detailRecord.rests && detailRecord.rests.length > 0">
              <h4>休憩時間</h4>
              <div 
                v-for="(rest, index) in detailRecord.rests" 
                :key="index"
                class="rest-item-readonly"
              >
                <span>{{ rest.rest_start }} - {{ rest.rest_end || '休憩中' }}</span>
              </div>
            </div>
          </div>

          <!-- 勤務完了の場合（編集可能） -->
          <form v-else-if="detailRecord.status === 'completed'" @submit.prevent="saveAttendanceDetail">
            <div class="form-group">
              <label>出勤時刻 *</label>
              <input 
                v-model="editingRecord.check_in" 
                type="time" 
                class="form-control"
                required
              />
            </div>

            <div class="form-group">
              <label>退勤時刻 *</label>
              <input 
                v-model="editingRecord.check_out" 
                type="time" 
                class="form-control"
                required
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
                    <span>削除</span>
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

          <!-- 閲覧のみの場合のボタン -->
          <div v-if="detailRecord.status !== 'completed'" class="modal-actions">
            <button type="button" @click="closeDetailModal" class="btn-cancel">
              閉じる
            </button>
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
      isLoading: false,
      showDetailModal: false,
      detailRecord: {},
      editingRecord: {
        id: null,
        attendance_id: null,
        check_in: '',
        check_out: '',
        rests: []
      }
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
      const year = today.getFullYear()
      const month = String(today.getMonth() + 1).padStart(2, '0')
      const day = String(today.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
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
      return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`
    },

    getTimeClass(timeString) {
      if (!timeString) return ''
      // 遅刻判定などのクラスを返す（必要に応じて拡張）
      return ''
    },

    async viewDetail(record) {
      this.detailRecord = { ...record }
      
      // 勤務完了の場合のみ、編集用データを準備
      if (record.status === 'completed') {
        // APIから詳細データを取得
        try {
          const response = await this.$axios.get(`/api/admin/attendance/daily/${record.user_id}`, {
            params: { date: this.selectedDate },
            headers: {
              Authorization: `Bearer ${this.$store.getters['auth/token']}`
            }
          })

          const attendance = response.data
          
          this.editingRecord = {
            id: attendance.id,
            attendance_id: attendance.id,
            check_in: attendance.check_in ? attendance.check_in.substring(0, 5) : '',
            check_out: attendance.check_out ? attendance.check_out.substring(0, 5) : '',
            rests: attendance.rests ? attendance.rests.map(rest => ({
              id: rest.id,
              rest_start: rest.rest_start ? rest.rest_start.substring(0, 5) : '',
              rest_end: rest.rest_end ? rest.rest_end.substring(0, 5) : ''
            })) : []
          }
        } catch (error) {
          console.error('勤怠詳細取得エラー:', error)
          if (this.$toast) {
            this.$toast.error('勤怠詳細の取得に失敗しました')
          }
          return
        }
      }
      
      this.showDetailModal = true
    },

    closeDetailModal() {
      this.showDetailModal = false
      this.detailRecord = {}
      this.editingRecord = {
        id: null,
        attendance_id: null,
        check_in: '',
        check_out: '',
        rests: []
      }
    },

    addRest() {
      this.editingRecord.rests.push({
        rest_start: '',
        rest_end: ''
      })
    },

    removeRest(index) {
      this.editingRecord.rests.splice(index, 1)
    },

    async saveAttendanceDetail() {
      try {
        await this.$axios.put(
          `/api/admin/attendance/${this.editingRecord.attendance_id}`,
          {
            check_in: this.editingRecord.check_in,
            check_out: this.editingRecord.check_out,
            rests: this.editingRecord.rests
          },
          {
            headers: {
              Authorization: `Bearer ${this.$store.getters['auth/token']}`
            }
          }
        )

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

    exportToCSV() {
      if (this.attendanceRecords.length === 0) {
        if (this.$toast) {
          this.$toast.warning('出力するデータがありません')
        }
        return
      }

      // CSVエスケープ関数
      const escapeCsv = (value) => {
        const str = String(value)
        if (str.includes(',') || str.includes('"') || str.includes('\n')) {
          return `"${str.replace(/"/g, '""')}"`
        }
        return str
      }

      // CSVヘッダー
      const headers = ['スタッフ名', 'スタッフID', '出勤時刻', '退勤時刻', '休憩時間', '勤務時間', 'ステータス']

      // CSVデータ行
      const rows = this.attendanceRecords.map(record => {
        return [
          record.user_name,
          record.user_id,
          this.formatTime(record.check_in_time),
          this.formatTime(record.check_out_time),
          this.formatDuration(record.break_time),
          this.formatDuration(record.work_time),
          this.getStatusLabel(record.status)
        ]
      })

      // CSV文字列生成
      const csvContent = [
        headers.map(escapeCsv).join(','),
        ...rows.map(row => row.map(escapeCsv).join(','))
      ].join('\r\n')

      // BOM付きCSV（Excel対応）
      const bom = '\uFEFF'
      const blob = new Blob([bom + csvContent], { type: 'text/csv;charset=utf-8;' })

      // ファイル名生成
      const dateLabel = this.formatDateLabel(this.selectedDate).replace(/[\/\\:*?"<>|]/g, '_')
      const filename = `${dateLabel}_日次勤怠データ.csv`

      // ダウンロード
      const link = document.createElement('a')
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
    },

    getStatusLabel(status) {
      const labels = {
        working: '勤務中',
        completed: '退勤済み',
        absent: '未出勤'
      }
      return labels[status] || status
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-daily-attendance.css" scoped></style>
