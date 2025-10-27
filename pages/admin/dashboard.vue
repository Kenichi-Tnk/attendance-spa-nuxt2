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
      
      <StatCard
        :value="stats.lateToday"
        label="本日遅刻者"
        icon="fas fa-exclamation-triangle"
        color="red"
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
        to="/admin/attendance/monthly"
        class="admin-dashboard__menu-card"
      >
        <div class="admin-dashboard__menu-content">
          <i class="fas fa-calendar-alt admin-dashboard__menu-icon admin-dashboard__menu-icon--monthly"></i>
          <h3 class="admin-dashboard__menu-title">月次勤怠一覧</h3>
        </div>
        <p class="admin-dashboard__menu-desc">スタッフ毎の月次勤怠を確認</p>
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
      
      <nuxt-link
        to="/admin/reports"
        class="admin-dashboard__menu-card"
      >
        <div class="admin-dashboard__menu-content">
          <i class="fas fa-chart-bar admin-dashboard__menu-icon admin-dashboard__menu-icon--reports"></i>
          <h3 class="admin-dashboard__menu-title">レポート</h3>
        </div>
        <p class="admin-dashboard__menu-desc">勤怠データの分析とレポート</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/settings"
        class="admin-dashboard__menu-card"
      >
        <div class="admin-dashboard__menu-content">
          <i class="fas fa-cog admin-dashboard__menu-icon admin-dashboard__menu-icon--settings"></i>
          <h3 class="admin-dashboard__menu-title">システム設定</h3>
        </div>
        <p class="admin-dashboard__menu-desc">勤怠管理システムの設定</p>
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
        totalStaff: 25,
        presentToday: 18,
        pendingRequests: 5,
        lateToday: 2
      },
      recentActivities: [
        {
          id: 1,
          icon: 'fas fa-user-plus',
          message: '田中太郎さんが出勤しました',
          time: '2分前'
        },
        {
          id: 2,
          icon: 'fas fa-edit',
          message: '佐藤花子さんが修正申請を提出しました',
          time: '15分前'
        },
        {
          id: 3,
          icon: 'fas fa-user-minus',
          message: '鈴木一郎さんが退勤しました',
          time: '30分前'
        },
        {
          id: 4,
          icon: 'fas fa-check',
          message: '勤怠修正申請を承認しました',
          time: '1時間前'
        }
      ]
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
        // TODO: API呼び出しでダッシュボードデータを取得
        await new Promise(resolve => setTimeout(resolve, 500))
      } catch (error) {
        console.error('ダッシュボードデータ取得エラー:', error)
      }
    }
  }
}
</script>

<style scoped>
@import '@/assets/css/pages/admin-dashboard.css';
</style>
