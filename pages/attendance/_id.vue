<template>
  <div class="attendance-detail__container">
    <div class="attendance-detail__header">
      <nav class="attendance-detail__breadcrumb">
        <nuxt-link to="/attendance" class="attendance-detail__breadcrumb-link">勤怠一覧</nuxt-link>
        <span>/</span>
        <span class="attendance-detail__breadcrumb-current">勤怠詳細</span>
      </nav>
      
      <h1 class="attendance-detail__title">勤怠詳細</h1>
      <p class="attendance-detail__subtitle">{{ formatFullDate(attendance.date) }}の勤怠記録</p>
    </div>
    
    <div class="attendance-detail__grid">
      <!-- 勤怠情報 -->
      <div class="attendance-detail__info-card">
        <h2 class="attendance-detail__card-title">勤怠情報</h2>
        
        <div class="attendance-detail__info-list">
          <div class="attendance-detail__info-item">
            <span class="attendance-detail__info-label">日付</span>
            <span class="attendance-detail__info-value">{{ formatFullDate(attendance.date) }}</span>
          </div>
          
          <div class="attendance-detail__info-item">
            <span class="attendance-detail__info-label">出勤時刻</span>
            <span :class="attendance.clockInLate ? 'attendance-detail__info-value--late' : 'attendance-detail__info-value'">
              {{ attendance.clockIn || '−' }}
              <i v-if="attendance.clockInLate" class="fas fa-exclamation-triangle ml-1"></i>
            </span>
          </div>
          
          <div class="attendance-detail__info-item">
            <span class="attendance-detail__info-label">退勤時刻</span>
            <span :class="attendance.clockOutEarly ? 'attendance-detail__info-value--early' : 'attendance-detail__info-value'">
              {{ attendance.clockOut || '−' }}
              <i v-if="attendance.clockOutEarly" class="fas fa-exclamation-triangle ml-1"></i>
            </span>
          </div>
          
          <div class="attendance-detail__info-item">
            <span class="attendance-detail__info-label">休憩時間</span>
            <span class="attendance-detail__info-value">{{ attendance.breakTime || '1:00' }}</span>
          </div>
          
          <div class="attendance-detail__info-item">
            <span class="attendance-detail__info-label">実労働時間</span>
            <span class="attendance-detail__info-value--work-hours">{{ attendance.workHours || '−' }}</span>
          </div>
          
          <div class="attendance-detail__info-item">
            <span class="attendance-detail__info-label">ステータス</span>
            <span :class="getStatusClass(attendance.status)" class="attendance-detail__status-badge">
              {{ getStatusText(attendance.status) }}
            </span>
          </div>
        </div>
        
        <!-- 操作ボタン -->
        <div class="attendance-detail__actions">
          <button
            v-if="canRequestCorrection"
            @click="requestCorrection"
            class="attendance-detail__correction-btn"
          >
            <i class="fas fa-edit mr-2"></i>
            修正申請
          </button>
          
          <nuxt-link
            to="/attendance"
            class="attendance-detail__back-btn"
          >
            <i class="fas fa-arrow-left mr-2"></i>
            一覧に戻る
          </nuxt-link>
        </div>
      </div>
      
      <!-- 修正履歴 -->
      <div class="attendance-detail__history-card">
        <h2 class="attendance-detail__card-title">修正履歴</h2>
        
        <div v-if="correctionHistory.length === 0" class="attendance-detail__empty-history">
          <i class="fas fa-history attendance-detail__empty-icon"></i>
          <p>修正履歴はありません</p>
        </div>
        
        <div v-else class="attendance-detail__history-list">
          <div v-for="correction in correctionHistory" :key="correction.id" class="attendance-detail__history-item">
            <div class="attendance-detail__history-header">
              <h4 class="attendance-detail__history-type">{{ correction.type }}</h4>
              <span :class="getCorrectionStatusClass(correction.status)" class="attendance-detail__history-status">
                {{ getCorrectionStatusText(correction.status) }}
              </span>
            </div>
            <p class="attendance-detail__history-reason">{{ correction.reason }}</p>
            <div class="attendance-detail__history-dates">
              <span>申請日時: {{ formatDateTime(correction.requestedAt) }}</span>
              <span v-if="correction.reviewedAt">承認日時: {{ formatDateTime(correction.reviewedAt) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AttendanceDetail',
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      attendance: {
        id: 1,
        date: '2024-10-25',
        clockIn: '09:15',
        clockOut: '18:30',
        workHours: '8:15',
        breakTime: '1:00',
        status: 'late',
        clockInLate: true,
        clockOutEarly: false
      },
      correctionHistory: [
        {
          id: 1,
          type: '出勤時刻修正',
          reason: '電車遅延のため',
          status: 'approved',
          requestedAt: '2024-10-26T09:30:00',
          reviewedAt: '2024-10-26T14:00:00'
        }
      ]
    }
  },
  
  computed: {
    canRequestCorrection() {
      return this.attendance.status !== 'pending'
    }
  },
  
  async mounted() {
    await this.loadAttendanceDetail()
  },
  
  methods: {
    async loadAttendanceDetail() {
      try {
        const id = this.$route.params.id
        
        // TODO: API呼び出しで勤怠詳細を取得
        await new Promise(resolve => setTimeout(resolve, 500))
        
        // モックデータはすでにdata()で設定済み
      } catch (error) {
        console.error('勤怠詳細取得エラー:', error)
        this.$toast.error('勤怠詳細の取得に失敗しました')
      }
    },
    
    formatFullDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long'
      })
    },
    
    formatDateTime(dateTimeString) {
      const date = new Date(dateTimeString)
      return date.toLocaleString('ja-JP')
    },
    
    getStatusClass(status) {
      const classes = {
        normal: 'attendance-detail__status-badge--normal',
        late: 'attendance-detail__status-badge--late',
        early_leave: 'attendance-detail__status-badge--early-leave',
        absent: 'attendance-detail__status-badge--absent',
        pending: 'attendance-detail__status-badge--pending'
      }
      return classes[status] || 'attendance-detail__status-badge'
    },
    
    getStatusText(status) {
      const texts = {
        normal: '正常',
        late: '遅刻',
        early_leave: '早退',
        absent: '欠席',
        pending: '承認待ち'
      }
      return texts[status] || '不明'
    },
    
    getCorrectionStatusClass(status) {
      const classes = {
        pending: 'attendance-detail__history-status--pending',
        approved: 'attendance-detail__history-status--approved',
        rejected: 'attendance-detail__history-status--rejected'
      }
      return classes[status] || 'attendance-detail__history-status'
    },
    
    getCorrectionStatusText(status) {
      const texts = {
        pending: '承認待ち',
        approved: '承認済み',
        rejected: '却下'
      }
      return texts[status] || '不明'
    },
    
    requestCorrection() {
      this.$router.push(`/correction-requests/new?attendance_id=${this.attendance.id}`)
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/attendance-detail.css';
</style>
