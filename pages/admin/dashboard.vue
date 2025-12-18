<template>
  <div class="admin-dashboard__container">
    <PageHeader
      title="管理者ダッシュボード"
      :subtitle="`${currentUser.name}さん（管理者）`"
      icon="fas fa-tachometer-alt"
      icon-color="#8B5CF6"
    />
    
    <!-- 統計サマリー -->
    <div class="admin-dashboard__stats-grid">
      <StatCard
        :value="stats.totalStaff"
        label="総スタッフ数"
        icon="fas fa-users"
        color="blue"
      />
      
      <StatCard
        :value="stats.presentToday"
        label="本日出勤中"
        icon="fas fa-user-check"
        color="green"
      />
      
      <StatCard
        :value="stats.pendingRequests"
        label="承認待ち申請"
        icon="fas fa-clock"
        color="yellow"
        :clickable="true"
        @click="$router.push('/admin/correction-requests')"
      />
    </div>
    
    <!-- 管理メニュー -->
    <div class="admin-dashboard__menu-grid">
      <nuxt-link
        to="/admin/attendance/daily"
        class="admin-dashboard__menu-card"
      >
        <div class="admin-dashboard__menu-content">
          <i class="fas fa-calendar-day admin-dashboard__menu-icon admin-dashboard__menu-icon--daily"></i>
          <h3 class="admin-dashboard__menu-title">日次勤怠一覧</h3>
        </div>
        <p class="admin-dashboard__menu-desc">日毎の全スタッフ勤怠状況を確認</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/staff"
        class="admin-dashboard__menu-card"
      >
        <div class="admin-dashboard__menu-content">
          <i class="fas fa-users admin-dashboard__menu-icon admin-dashboard__menu-icon--staff"></i>
          <h3 class="admin-dashboard__menu-title">スタッフ一覧</h3>
        </div>
        <p class="admin-dashboard__menu-desc">登録されているスタッフの管理</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/correction-requests"
        class="admin-dashboard__menu-card"
      >
        <div class="admin-dashboard__menu-content">
          <i class="fas fa-edit admin-dashboard__menu-icon admin-dashboard__menu-icon--requests"></i>
          <h3 class="admin-dashboard__menu-title">修正申請管理</h3>
        </div>
        <p class="admin-dashboard__menu-desc">スタッフからの修正申請を管理</p>
      </nuxt-link>
    </div>
    
    <!-- 最近の活動 -->
    <div class="admin-dashboard__activity-section">
      <h2 class="admin-dashboard__activity-title">最近の活動</h2>
      <div class="admin-dashboard__activity-list">
        <div v-for="activity in recentActivities" :key="activity.id" class="admin-dashboard__activity-item">
          <i :class="activity.icon" class="admin-dashboard__activity-icon"></i>
          <div class="admin-dashboard__activity-content">
            <p class="admin-dashboard__activity-message">{{ activity.message }}</p>
            <p class="admin-dashboard__activity-time">{{ activity.time }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PageHeader from '~/components/PageHeader.vue'
import StatCard from '~/components/StatCard.vue'

export default {
  name: 'AdminDashboard',
  components: {
    PageHeader,
    StatCard
  },
  middleware: ['auth', 'verified', 'admin'],
  
  data() {
    return {
      stats: {
        totalStaff: 0,
        presentToday: 0,
        pendingRequests: 0
      },
      recentActivities: []
    }
  },
  
  computed: {
    currentUser() {
      return this.$store.getters['auth/user'] || {}
    }
  },
  
  mounted() {
    this.loadDashboardData()
  },
  
  methods: {
    async loadDashboardData() {
      try {
        const response = await this.$axios.get('/api/admin/dashboard', {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.stats = response.data.stats
        this.recentActivities = response.data.recentActivities || []
      } catch (error) {
        console.error('ダッシュボードデータ取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('ダッシュボードデータの取得に失敗しました')
        }
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/admin-dashboard.css';
</style>
