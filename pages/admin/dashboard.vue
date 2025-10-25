<template>
  <div class="container mx-auto px-4 py-8">
    <PageHeader
      title="管理者ダッシュボード"
      :subtitle="`${currentUser.name}さん（管理者）`"
      icon="fas fa-tachometer-alt"
      icon-color="#8B5CF6"
    />
    
    <!-- 統計サマリー -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <nuxt-link
        to="/admin/attendance/daily"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center mb-4">
          <i class="fas fa-calendar-day text-blue-500 text-2xl mr-4"></i>
          <h3 class="text-lg font-semibold text-gray-800">日次勤怠一覧</h3>
        </div>
        <p class="text-gray-600 text-sm">日毎の全スタッフ勤怠状況を確認</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/staff"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center mb-4">
          <i class="fas fa-users text-green-500 text-2xl mr-4"></i>
          <h3 class="text-lg font-semibold text-gray-800">スタッフ一覧</h3>
        </div>
        <p class="text-gray-600 text-sm">登録されているスタッフの管理</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/attendance/monthly"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center mb-4">
          <i class="fas fa-calendar-alt text-purple-500 text-2xl mr-4"></i>
          <h3 class="text-lg font-semibold text-gray-800">月次勤怠一覧</h3>
        </div>
        <p class="text-gray-600 text-sm">スタッフ毎の月次勤怠を確認</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/correction-requests"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center mb-4">
          <i class="fas fa-edit text-yellow-500 text-2xl mr-4"></i>
          <h3 class="text-lg font-semibold text-gray-800">修正申請管理</h3>
        </div>
        <p class="text-gray-600 text-sm">スタッフからの修正申請を管理</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/reports"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center mb-4">
          <i class="fas fa-chart-bar text-indigo-500 text-2xl mr-4"></i>
          <h3 class="text-lg font-semibold text-gray-800">レポート</h3>
        </div>
        <p class="text-gray-600 text-sm">勤怠データの分析とレポート</p>
      </nuxt-link>
      
      <nuxt-link
        to="/admin/settings"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-center mb-4">
          <i class="fas fa-cog text-gray-500 text-2xl mr-4"></i>
          <h3 class="text-lg font-semibold text-gray-800">システム設定</h3>
        </div>
        <p class="text-gray-600 text-sm">勤怠管理システムの設定</p>
      </nuxt-link>
    </div>
    
    <!-- 最近の活動 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">最近の活動</h2>
      <div class="space-y-3">
        <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center p-3 bg-gray-50 rounded-lg">
          <i :class="activity.icon" class="text-gray-500 mr-3"></i>
          <div class="flex-1">
            <p class="text-sm text-gray-800">{{ activity.message }}</p>
            <p class="text-xs text-gray-500">{{ activity.time }}</p>
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
