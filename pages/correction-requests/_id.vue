<template>
  <div class="correction-request-detail">
    <PageHeader
      title="修正申請詳細"
      :breadcrumbs="breadcrumbs"
    />
    
    <div v-if="isLoading" class="loading-container">
      <LoadingSpinner />
    </div>
    
    <div v-else-if="correctionRequest" class="correction-request-detail__container">
      <!-- 申請情報 -->
      <div class="detail-section">
        <h3 class="section-title">申請情報</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <span class="label">申請ID</span>
            <span class="value">#{{ correctionRequest.id }}</span>
          </div>
          <div class="detail-item">
            <span class="label">対象日</span>
            <span class="value">{{ formatDate(correctionRequest.date) }}</span>
          </div>
          <div class="detail-item">
            <span class="label">申請日時</span>
            <span class="value">{{ formatDateTime(correctionRequest.created_at) }}</span>
          </div>
          <div class="detail-item">
            <span class="label">ステータス</span>
            <StatusBadge :status="correctionRequest.status" />
          </div>
        </div>
      </div>

      <!-- 修正内容 -->
      <div class="detail-section">
        <h3 class="section-title">修正内容</h3>
        <div class="correction-comparison">
          <!-- 修正前 -->
          <div class="comparison-column">
            <h4 class="comparison-title">修正前</h4>
            <div v-if="originalAttendance" class="attendance-data">
              <div class="attendance-item">
                <span class="label">出勤時刻</span>
                <span class="value">{{ formatTime(originalAttendance.check_in) }}</span>
              </div>
              <div class="attendance-item">
                <span class="label">退勤時刻</span>
                <span class="value">{{ formatTime(originalAttendance.check_out) || '未退勤' }}</span>
              </div>
              <div class="attendance-item">
                <span class="label">労働時間</span>
                <span class="value">{{ calculateWorkHours(originalAttendance) }}</span>
              </div>
              
              <!-- 休憩時間（修正前） -->
              <div v-if="originalAttendance.rests && originalAttendance.rests.length > 0" class="rest-times">
                <h5>休憩時間</h5>
                <div v-for="(rest, index) in originalAttendance.rests" :key="index" class="rest-item">
                  {{ formatTime(rest.rest_start) }} - {{ formatTime(rest.rest_end) }}
                </div>
              </div>
            </div>
          </div>

          <!-- 矢印 -->
          <div class="comparison-arrow">
            <i class="fas fa-arrow-right"></i>
          </div>

          <!-- 修正後 -->
          <div class="comparison-column">
            <h4 class="comparison-title">修正後</h4>
            <div class="attendance-data">
              <div class="attendance-item">
                <span class="label">出勤時刻</span>
                <span class="value modified">{{ formatTime(correctionRequest.check_in) }}</span>
              </div>
              <div class="attendance-item">
                <span class="label">退勤時刻</span>
                <span class="value modified">{{ formatTime(correctionRequest.check_out) || '未設定' }}</span>
              </div>
              <div class="attendance-item">
                <span class="label">労働時間</span>
                <span class="value modified">{{ calculateWorkHours(correctionRequest) }}</span>
              </div>
              
              <!-- 休憩時間（修正後） -->
              <div v-if="correctionRequest.rests && correctionRequest.rests.length > 0" class="rest-times">
                <h5>休憩時間</h5>
                <div v-for="(rest, index) in correctionRequest.rests" :key="index" class="rest-item modified">
                  {{ formatTime(rest.rest_start) }} - {{ formatTime(rest.rest_end) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 申請理由 -->
      <div class="detail-section">
        <h3 class="section-title">申請理由</h3>
        <div class="reason-content">
          {{ correctionRequest.reason }}
        </div>
      </div>

      <!-- 却下理由（却下された場合のみ） -->
      <div v-if="correctionRequest.status === 'rejected' && correctionRequest.reject_reason" class="detail-section">
        <h3 class="section-title">却下理由</h3>
        <div class="reject-reason-content">
          {{ correctionRequest.reject_reason }}
        </div>
      </div>

      <!-- アクション -->
      <div class="detail-actions">
        <nuxt-link
          to="/correction-requests"
          class="btn btn--secondary"
        >
          <i class="fas fa-arrow-left"></i>
          一覧に戻る
        </nuxt-link>
      </div>
    </div>
    
    <div v-else class="error-container">
      <div class="error-content">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>申請が見つかりません</h3>
        <p>指定された申請は存在しないか、アクセス権限がありません。</p>
        <nuxt-link
          to="/correction-requests"
          class="btn btn--primary"
        >
          一覧に戻る
        </nuxt-link>
      </div>
    </div>
  </div>
</template>

<script>
import PageHeader from '~/components/PageHeader.vue'
import StatusBadge from '~/components/StatusBadge.vue'
import LoadingSpinner from '~/components/LoadingSpinner.vue'

export default {
  name: 'CorrectionRequestDetail',
  components: {
    PageHeader,
    StatusBadge,
    LoadingSpinner
  },
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      correctionRequest: null,
      originalAttendance: null,
      isLoading: true
    }
  },
  
  computed: {
    breadcrumbs() {
      return [
        { text: 'ダッシュボード', to: '/dashboard' },
        { text: '修正申請', to: '/correction-requests' },
        { text: '詳細' }
      ]
    }
  },
  
  async mounted() {
    await this.loadCorrectionRequest()
  },
  
  methods: {
    async loadCorrectionRequest() {
      try {
        this.isLoading = true
        const id = this.$route.params.id
        
        // 申請詳細を取得
        const response = await this.$axios.$get(`/api/correction-requests/${id}`)
        this.correctionRequest = response.correction
        
        // 元の勤怠データを取得
        await this.loadOriginalAttendance()
      } catch (error) {
        console.error('申請詳細取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('申請詳細の取得に失敗しました')
        }
      } finally {
        this.isLoading = false
      }
    },
    
    async loadOriginalAttendance() {
      try {
        if (!this.correctionRequest) return
        
        const response = await this.$axios.$get('/api/attendance', {
          params: {
            date: this.correctionRequest.date
          }
        })
        
        // その日の勤怠データを見つける
        this.originalAttendance = response.data.find(item => 
          item.date === this.correctionRequest.date
        )
      } catch (error) {
        console.error('元勤怠データ取得エラー:', error)
      }
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'short'
      })
    },
    
    formatDateTime(dateTimeString) {
      const date = new Date(dateTimeString)
      return date.toLocaleString('ja-JP')
    },
    
    formatTime(timeString) {
      if (!timeString) return ''
      return timeString.substring(0, 5)
    },
    
    calculateWorkHours(attendance) {
      if (!attendance.check_in || !attendance.check_out) {
        return '−'
      }
      
      const dateStr = attendance.date.split('T')[0]
      const startTime = new Date(`${dateStr}T${attendance.check_in}`)
      const endTime = new Date(`${dateStr}T${attendance.check_out}`)
      
      if (isNaN(startTime.getTime()) || isNaN(endTime.getTime())) {
        return '−'
      }
      
      let workMilliseconds = endTime - startTime
      
      // 休憩時間を差し引く
      if (attendance.rests && attendance.rests.length > 0) {
        const totalBreakMilliseconds = attendance.rests.reduce((total, rest) => {
          if (rest.rest_start && rest.rest_end) {
            const restStart = new Date(`${dateStr}T${rest.rest_start}`)
            const restEnd = new Date(`${dateStr}T${rest.rest_end}`)
            
            if (!isNaN(restStart.getTime()) && !isNaN(restEnd.getTime())) {
              return total + (restEnd - restStart)
            }
          }
          return total
        }, 0)
        
        workMilliseconds -= totalBreakMilliseconds
      }
      
      if (workMilliseconds <= 0) return '0:00'
      
      const hours = Math.floor(workMilliseconds / (1000 * 60 * 60))
      const minutes = Math.floor((workMilliseconds % (1000 * 60 * 60)) / (1000 * 60))
      
      return `${hours}:${String(minutes).padStart(2, '0')}`
    }
  }
}
</script>

<style scoped>
.correction-request-detail {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}

.correction-request-detail__container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: 60px 0;
}

.error-container {
  display: flex;
  justify-content: center;
  padding: 60px 0;
}

.error-content {
  text-align: center;
  max-width: 400px;
}

.error-content i {
  font-size: 48px;
  color: #ef4444;
  margin-bottom: 16px;
}

.error-content h3 {
  font-size: 20px;
  margin-bottom: 8px;
  color: #374151;
}

.error-content p {
  color: #6b7280;
  margin-bottom: 24px;
}

.detail-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 16px;
  color: #374151;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
}

.detail-item .label {
  font-weight: 500;
  color: #6b7280;
}

.detail-item .value {
  font-weight: 600;
  color: #374151;
}

.correction-comparison {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 24px;
  align-items: start;
}

.comparison-column {
  background: #f9fafb;
  border-radius: 8px;
  padding: 20px;
}

.comparison-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 16px;
  text-align: center;
  color: #374151;
}

.comparison-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  font-size: 24px;
  padding-top: 40px;
}

.attendance-data {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.attendance-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.attendance-item:last-child {
  border-bottom: none;
}

.attendance-item .label {
  font-weight: 500;
  color: #6b7280;
}

.attendance-item .value {
  font-weight: 600;
  color: #374151;
}

.attendance-item .value.modified {
  color: #059669;
  background: #d1fae5;
  padding: 4px 8px;
  border-radius: 4px;
}

.rest-times {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.rest-times h5 {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #6b7280;
}

.rest-item {
  padding: 6px 0;
  font-size: 14px;
  color: #374151;
}

.rest-item.modified {
  color: #059669;
  font-weight: 500;
}

.reason-content,
.reject-reason-content {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 16px;
  color: #374151;
  line-height: 1.6;
  white-space: pre-wrap;
}

.reject-reason-content {
  background: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

.detail-actions {
  display: flex;
  justify-content: flex-start;
  gap: 12px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn--secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn--secondary:hover {
  background: #e5e7eb;
}

.btn--primary {
  background: #3b82f6;
  color: white;
}

.btn--primary:hover {
  background: #2563eb;
}

@media (max-width: 768px) {
  .correction-comparison {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .comparison-arrow {
    transform: rotate(90deg);
    padding-top: 0;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>