<template>
  <div class="container mx-auto px-4 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">勤怠管理ダッシュボード</h1>
      <p class="text-gray-600 mt-2">{{ currentUser.name }}さん、お疲れ様です</p>
    </div>
    
    <!-- 勤怠打刻セクション -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">勤怠打刻</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="text-center">
          <button
            @click="clockIn"
            :disabled="isLoading || isWorking"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors"
          >
            <i class="fas fa-sign-in-alt mr-2"></i>
            出勤
          </button>
          <p class="text-sm text-gray-500 mt-2">勤務開始時に押してください</p>
        </div>
        
        <div class="text-center">
          <button
            @click="clockOut"
            :disabled="isLoading || !isWorking"
            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors"
          >
            <i class="fas fa-sign-out-alt mr-2"></i>
            退勤
          </button>
          <p class="text-sm text-gray-500 mt-2">勤務終了時に押してください</p>
        </div>
      </div>
      
      <!-- 現在の勤務状態 -->
      <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-sm text-gray-600">現在の状態:</span>
            <span :class="isWorking ? 'text-green-600 font-semibold' : 'text-gray-600'">
              {{ isWorking ? '勤務中' : '勤務外' }}
            </span>
          </div>
          <div v-if="todayStartTime" class="text-sm text-gray-600">
            出勤時刻: <span class="font-semibold">{{ todayStartTime }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- クイックアクション -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <nuxt-link
        to="/attendance"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-blue-500"
      >
        <div class="flex items-center">
          <i class="fas fa-calendar-alt text-blue-500 text-2xl mr-4"></i>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">勤怠一覧</h3>
            <p class="text-gray-600 text-sm">過去の勤怠記録を確認</p>
          </div>
        </div>
      </nuxt-link>
      
      <nuxt-link
        to="/correction-requests"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-yellow-500"
      >
        <div class="flex items-center">
          <i class="fas fa-edit text-yellow-500 text-2xl mr-4"></i>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">修正申請</h3>
            <p class="text-gray-600 text-sm">勤怠の修正を申請</p>
          </div>
        </div>
      </nuxt-link>
      
      <nuxt-link
        to="/my-requests"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-green-500"
      >
        <div class="flex items-center">
          <i class="fas fa-list text-green-500 text-2xl mr-4"></i>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">申請状況</h3>
            <p class="text-gray-600 text-sm">申請の進捗を確認</p>
          </div>
        </div>
      </nuxt-link>
    </div>
    
    <!-- 今月の勤怠サマリー -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">今月の勤怠サマリー</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-blue-50 rounded-lg">
          <div class="text-2xl font-bold text-blue-600">{{ monthlyStats.workDays }}</div>
          <div class="text-sm text-gray-600">出勤日数</div>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
          <div class="text-2xl font-bold text-green-600">{{ monthlyStats.totalHours }}</div>
          <div class="text-sm text-gray-600">総労働時間</div>
        </div>
        <div class="text-center p-4 bg-yellow-50 rounded-lg">
          <div class="text-2xl font-bold text-yellow-600">{{ monthlyStats.averageHours }}</div>
          <div class="text-sm text-gray-600">平均労働時間</div>
        </div>
        <div class="text-center p-4 bg-red-50 rounded-lg">
          <div class="text-2xl font-bold text-red-600">{{ monthlyStats.pendingRequests }}</div>
          <div class="text-sm text-gray-600">承認待ち申請</div>
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
