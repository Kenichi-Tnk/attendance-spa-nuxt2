<template>
  <div class="dashboard__container">
    <div class="dashboard__header">
      <h1 class="dashboard__title">勤怠管理ダッシュボード</h1>
      <p class="dashboard__subtitle">{{ currentUser.name }}さん、お疲れ様です</p>
    </div>
    
    <!-- 勤怠打刻セクション -->
    <div class="dashboard__clock-section">
      <TimeClock />
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
  
  async mounted() {
    // TimeClock.vueで既にfetchStatusを実行しているため、ここでは不要
    // await this.$store.dispatch('attendance/fetchTodayAttendance')
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/dashboard.css';
</style>
