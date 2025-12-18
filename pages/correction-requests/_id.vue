<template>
  <div class="correction-request-detail">
    <div class="correction-request-detail__page-header">
      <h1 class="correction-request-detail__title">修正申請詳細</h1>
    </div>
    
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
            <StatusBadge :status="correctionRequest.status" type="request" />
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
        
        // APIから修正前の勤怠データを取得
        const response = await this.$axios.$get(
          `/api/correction-requests/${this.correctionRequest.id}/original-attendance`
        )
        
        if (response.original_attendance) {
          this.originalAttendance = response.original_attendance
        }
      } catch (error) {
        console.error('元勤怠データ取得エラー:', error)
        // エラー時は空のオブジェクトをセット
        this.originalAttendance = null
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
    
    extractTimeOnly(timeString) {
      if (!timeString) return ''
      
      try {
        if (typeof timeString === 'string') {
          // "2025-11-17T09:15:00.000000Z" のような形式
          if (timeString.includes('T')) {
            const timePart = timeString.split('T')[1]
            if (timePart) {
              return timePart.split('.')[0] // ミリ秒を除去
            }
          }
          // "2025-11-17 09:15:00" のような形式
          if (timeString.includes(' ')) {
            const timePart = timeString.split(' ')[1]
            if (timePart) {
              return timePart
            }
          }
          // "09:15:00" のような時刻のみの場合
          return timeString
        }
        return timeString
      } catch (error) {
        console.error('時刻抽出エラー:', error, timeString)
        return ''
      }
    },
    
    formatTime(timeString) {
      if (!timeString) return ''
      
      try {
        // ISO形式の日時文字列から時刻部分を抽出
        if (typeof timeString === 'string') {
          // "2025-11-17T09:15:00.000000Z" のような形式の場合
          if (timeString.includes('T')) {
            const timePart = timeString.split('T')[1]
            if (timePart) {
              return timePart.substring(0, 5)
            }
          }
          // "2025-11-17 09:15:00" のような形式の場合
          if (timeString.includes(' ')) {
            const timePart = timeString.split(' ')[1]
            if (timePart) {
              return timePart.substring(0, 5)
            }
          }
          // "09:15:00" のような時刻のみの場合
          if (timeString.includes(':')) {
            return timeString.substring(0, 5)
          }
        }
        
        return timeString.substring(0, 5)
      } catch (error) {
        console.error('時刻フォーマットエラー:', error, timeString)
        return ''
      }
    },
    
    calculateWorkHours(attendance) {
      if (!attendance.check_in || !attendance.check_out) {
        return '−'
      }
      
      // 日付文字列を取得
      let dateStr = attendance.date
      if (typeof dateStr === 'string') {
        // ISO形式の場合は日付部分のみ抽出
        if (dateStr.includes('T')) {
          dateStr = dateStr.split('T')[0]
        } else if (dateStr.includes(' ')) {
          dateStr = dateStr.split(' ')[0]
        }
      }
      
      // 時刻部分を抽出
      const checkIn = this.extractTimeOnly(attendance.check_in)
      const checkOut = this.extractTimeOnly(attendance.check_out)
      
      const startTime = new Date(`${dateStr}T${checkIn}`)
      const endTime = new Date(`${dateStr}T${checkOut}`)
      
      if (isNaN(startTime.getTime()) || isNaN(endTime.getTime())) {
        return '−'
      }
      
      let workMilliseconds = endTime - startTime
      
      // 休憩時間を差し引く
      if (attendance.rests && attendance.rests.length > 0) {
        const totalBreakMilliseconds = attendance.rests.reduce((total, rest) => {
          if (rest.rest_start && rest.rest_end) {
            const restStart = new Date(`${dateStr}T${this.extractTimeOnly(rest.rest_start)}`)
            const restEnd = new Date(`${dateStr}T${this.extractTimeOnly(rest.rest_end)}`)
            
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

<style src="@/assets/css/pages/correction-requests-detail.css" scoped></style>