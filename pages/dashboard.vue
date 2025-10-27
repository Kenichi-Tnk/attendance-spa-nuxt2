<template>
  <div class="dashboard__container">
    <div class="dashboard__header">
      <h1 class="dashboard__title">勤怠管理ダッシュボード</h1>
      <p class="dashboard__subtitle">{{ currentUser.name }}さん、お疲れ様です</p>
    </div>
    
    <!-- 勤怠打刻セクション -->
    <div class="dashboard__clock-section">
      <h2 class="dashboard__clock-title">勤怠打刻</h2>
      <div class="dashboard__clock-grid">
        <div class="dashboard__clock-button-container">
          <button
            @click="clockIn"
            :disabled="isLoading || isWorking"
            class="dashboard__clock-in-btn"
          >
            <i class="fas fa-sign-in-alt mr-2"></i>
            出勤
          </button>
          <p class="dashboard__clock-help-text">勤務開始時に押してください</p>
        </div>
        
        <div class="dashboard__clock-button-container">
          <button
            @click="clockOut"
            :disabled="isLoading || !isWorking"
            class="dashboard__clock-out-btn"
          >
            <i class="fas fa-sign-out-alt mr-2"></i>
            退勤
          </button>
          <p class="dashboard__clock-help-text">勤務終了時に押してください</p>
        </div>
      </div>
      
      <!-- 現在の勤務状態 -->
      <div class="dashboard__status-section">
        <div class="dashboard__status-row">
          <div>
            <span class="dashboard__status-label">現在の状態:</span>
            <span :class="isWorking ? 'dashboard__status-working' : 'dashboard__status-not-working'">
              {{ isWorking ? '勤務中' : '勤務外' }}
            </span>
          </div>
          <div v-if="todayStartTime" class="dashboard__start-time">
            出勤時刻: <span class="dashboard__start-time-value">{{ todayStartTime }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- クイックアクション -->
    <div class="dashboard__actions-grid">
      <nuxt-link
        to="/attendance"
        class="dashboard__action-card dashboard__action-card--attendance"
      >
        <div class="dashboard__action-content">
          <i class="fas fa-calendar-alt dashboard__action-icon dashboard__action-icon--attendance"></i>
          <div>
            <h3 class="dashboard__action-title">勤怠一覧</h3>
            <p class="dashboard__action-desc">過去の勤怠記録を確認</p>
          </div>
        </div>
      </nuxt-link>
      
      <nuxt-link
        to="/correction-requests"
        class="dashboard__action-card dashboard__action-card--correction"
      >
        <div class="dashboard__action-content">
          <i class="fas fa-edit dashboard__action-icon dashboard__action-icon--correction"></i>
          <div>
            <h3 class="dashboard__action-title">修正申請</h3>
            <p class="dashboard__action-desc">勤怠の修正を申請</p>
          </div>
        </div>
      </nuxt-link>
      
      <nuxt-link
        to="/my-requests"
        class="dashboard__action-card dashboard__action-card--requests"
      >
        <div class="dashboard__action-content">
          <i class="fas fa-list dashboard__action-icon dashboard__action-icon--requests"></i>
          <div>
            <h3 class="dashboard__action-title">申請状況</h3>
            <p class="dashboard__action-desc">申請の進捗を確認</p>
          </div>
        </div>
      </nuxt-link>
    </div>
    
    <!-- 今月の勤怠サマリー -->
    <div class="dashboard__summary-section">
      <h2 class="dashboard__summary-title">今月の勤怠サマリー</h2>
      <div class="dashboard__summary-grid">
        <div class="dashboard__stat-card dashboard__stat-card--work-days">
          <div class="dashboard__stat-value dashboard__stat-value--work-days">{{ monthlyStats.workDays }}</div>
          <div class="dashboard__stat-label">出勤日数</div>
        </div>
        <div class="dashboard__stat-card dashboard__stat-card--total-hours">
          <div class="dashboard__stat-value dashboard__stat-value--total-hours">{{ monthlyStats.totalHours }}</div>
          <div class="dashboard__stat-label">総労働時間</div>
        </div>
        <div class="dashboard__stat-card dashboard__stat-card--average-hours">
          <div class="dashboard__stat-value dashboard__stat-value--average-hours">{{ monthlyStats.averageHours }}</div>
          <div class="dashboard__stat-label">平均労働時間</div>
        </div>
        <div class="dashboard__stat-card dashboard__stat-card--pending">
          <div class="dashboard__stat-value dashboard__stat-value--pending">{{ monthlyStats.pendingRequests }}</div>
          <div class="dashboard__stat-label">承認待ち申請</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      isLoading: false,
      isWorking: false,
      todayStartTime: null,
      monthlyStats: {
        workDays: 20,
        totalHours: '160.5h',
        averageHours: '8.0h',
        pendingRequests: 2
      }
    }
  },
  
  computed: {
    currentUser() {
      return this.$store.getters['auth/user'] || {}
    }
  },
  
  mounted() {
    this.loadTodayAttendance()
  },
  
  methods: {
    async clockIn() {
      try {
        this.isLoading = true
        
        // TODO: API呼び出し
        await new Promise(resolve => setTimeout(resolve, 500))
        
        this.isWorking = true
        this.todayStartTime = new Date().toLocaleTimeString('ja-JP', {
          hour: '2-digit',
          minute: '2-digit'
        })
        
        this.$toast.success('出勤打刻が完了しました')
      } catch (error) {
        console.error('出勤打刻エラー:', error)
        this.$toast.error('出勤打刻に失敗しました')
      } finally {
        this.isLoading = false
      }
    },
    
    async clockOut() {
      try {
        this.isLoading = true
        
        // TODO: API呼び出し
        await new Promise(resolve => setTimeout(resolve, 500))
        
        this.isWorking = false
        
        this.$toast.success('退勤打刻が完了しました')
      } catch (error) {
        console.error('退勤打刻エラー:', error)
        this.$toast.error('退勤打刻に失敗しました')
      } finally {
        this.isLoading = false
      }
    },
    
    async loadTodayAttendance() {
      try {
        // TODO: API呼び出しで今日の勤怠状況を取得
        await new Promise(resolve => setTimeout(resolve, 300))
        
        // モックデータ
        const todayAttendance = {
          isWorking: false,
          startTime: null
        }
        
        this.isWorking = todayAttendance.isWorking
        this.todayStartTime = todayAttendance.startTime
      } catch (error) {
        console.error('勤怠状況取得エラー:', error)
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/dashboard.css';
</style>
