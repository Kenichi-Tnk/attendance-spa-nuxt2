&lt;template&gt;
  &lt;div class="container mx-auto px-4 py-8"&gt;
    &lt;PageHeader
      title="管理者ダッシュボード"
      :subtitle="`${currentUser.name}さん（管理者）`"
      icon="fas fa-tachometer-alt"
      icon-color="#8B5CF6"
    /&gt;
    
    &lt;!-- 統計サマリー --&gt;
    &lt;div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8"&gt;
      &lt;StatCard
        :value="stats.totalStaff"
        label="総スタッフ数"
        icon="fas fa-users"
        color="blue"
      /&gt;
      
      &lt;StatCard
        :value="stats.presentToday"
        label="本日出勤中"
        icon="fas fa-user-check"
        color="green"
      /&gt;
      
      &lt;StatCard
        :value="stats.pendingRequests"
        label="承認待ち申請"
        icon="fas fa-clock"
        color="yellow"
        :clickable="true"
        @click="$router.push('/admin/correction-requests')"
      /&gt;
      
      &lt;StatCard
        :value="stats.lateToday"
        label="本日遅刻者"
        icon="fas fa-exclamation-triangle"
        color="red"
      /&gt;
    &lt;/div&gt;
    
    &lt;!-- 管理メニュー --&gt;
    &lt;div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"&gt;
      &lt;nuxt-link
        to="/admin/attendance/daily"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      &gt;
        &lt;div class="flex items-center mb-4"&gt;
          &lt;i class="fas fa-calendar-day text-blue-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;h3 class="text-lg font-semibold text-gray-800"&gt;日次勤怠一覧&lt;/h3&gt;
        &lt;/div&gt;
        &lt;p class="text-gray-600 text-sm"&gt;日毎の全スタッフ勤怠状況を確認&lt;/p&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/admin/staff"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      &gt;
        &lt;div class="flex items-center mb-4"&gt;
          &lt;i class="fas fa-users text-green-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;h3 class="text-lg font-semibold text-gray-800"&gt;スタッフ一覧&lt;/h3&gt;
        &lt;/div&gt;
        &lt;p class="text-gray-600 text-sm"&gt;登録されているスタッフの管理&lt;/p&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/admin/attendance/monthly"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      &gt;
        &lt;div class="flex items-center mb-4"&gt;
          &lt;i class="fas fa-calendar-alt text-purple-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;h3 class="text-lg font-semibold text-gray-800"&gt;月次勤怠一覧&lt;/h3&gt;
        &lt;/div&gt;
        &lt;p class="text-gray-600 text-sm"&gt;スタッフ毎の月次勤怠を確認&lt;/p&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/admin/correction-requests"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      &gt;
        &lt;div class="flex items-center mb-4"&gt;
          &lt;i class="fas fa-edit text-yellow-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;h3 class="text-lg font-semibold text-gray-800"&gt;修正申請管理&lt;/h3&gt;
        &lt;/div&gt;
        &lt;p class="text-gray-600 text-sm"&gt;スタッフからの修正申請を管理&lt;/p&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/admin/reports"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      &gt;
        &lt;div class="flex items-center mb-4"&gt;
          &lt;i class="fas fa-chart-bar text-indigo-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;h3 class="text-lg font-semibold text-gray-800"&gt;レポート&lt;/h3&gt;
        &lt;/div&gt;
        &lt;p class="text-gray-600 text-sm"&gt;勤怠データの分析とレポート&lt;/p&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/admin/settings"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      &gt;
        &lt;div class="flex items-center mb-4"&gt;
          &lt;i class="fas fa-cog text-gray-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;h3 class="text-lg font-semibold text-gray-800"&gt;システム設定&lt;/h3&gt;
        &lt;/div&gt;
        &lt;p class="text-gray-600 text-sm"&gt;勤怠管理システムの設定&lt;/p&gt;
      &lt;/nuxt-link&gt;
    &lt;/div&gt;
    
    &lt;!-- 最近の活動 --&gt;
    &lt;div class="bg-white rounded-lg shadow-md p-6"&gt;
      &lt;h2 class="text-xl font-semibold text-gray-800 mb-4"&gt;最近の活動&lt;/h2&gt;
      &lt;div class="space-y-3"&gt;
        &lt;div v-for="activity in recentActivities" :key="activity.id" class="flex items-center p-3 bg-gray-50 rounded-lg"&gt;
          &lt;i :class="activity.icon" class="text-gray-500 mr-3"&gt;&lt;/i&gt;
          &lt;div class="flex-1"&gt;
            &lt;p class="text-sm text-gray-800"&gt;{{ activity.message }}&lt;/p&gt;
            &lt;p class="text-xs text-gray-500"&gt;{{ activity.time }}&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
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
          message: '山田次郎さんが退勤しました',
          time: '30分前'
        },
        {
          id: 4,
          icon: 'fas fa-check',
          message: '修正申請が承認されました',
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
        await new Promise(resolve =&gt; setTimeout(resolve, 500))
      } catch (error) {
        console.error('ダッシュボードデータ取得エラー:', error)
      }
    }
  }
}
&lt;/script&gt;