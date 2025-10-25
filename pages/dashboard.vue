&lt;template&gt;
  &lt;div class="container mx-auto px-4 py-8"&gt;
    &lt;div class="mb-8"&gt;
      &lt;h1 class="text-3xl font-bold text-gray-900"&gt;勤怠管理ダッシュボード&lt;/h1&gt;
      &lt;p class="text-gray-600 mt-2"&gt;{{ currentUser.name }}さん、お疲れ様です&lt;/p&gt;
    &lt;/div&gt;
    
    &lt;!-- 勤怠打刻セクション --&gt;
    &lt;div class="bg-white rounded-lg shadow-md p-6 mb-8"&gt;
      &lt;h2 class="text-xl font-semibold text-gray-800 mb-4"&gt;勤怠打刻&lt;/h2&gt;
      &lt;div class="grid grid-cols-1 md:grid-cols-2 gap-4"&gt;
        &lt;div class="text-center"&gt;
          &lt;button
            @click="clockIn"
            :disabled="isWorking || isLoading"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors"
          &gt;
            &lt;i class="fas fa-sign-in-alt mr-2"&gt;&lt;/i&gt;
            出勤
          &lt;/button&gt;
          &lt;p class="text-sm text-gray-500 mt-2"&gt;勤務開始時に押してください&lt;/p&gt;
        &lt;/div&gt;
        
        &lt;div class="text-center"&gt;
          &lt;button
            @click="clockOut"
            :disabled="!isWorking || isLoading"
            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg text-lg transition-colors"
          &gt;
            &lt;i class="fas fa-sign-out-alt mr-2"&gt;&lt;/i&gt;
            退勤
          &lt;/button&gt;
          &lt;p class="text-sm text-gray-500 mt-2"&gt;勤務終了時に押してください&lt;/p&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;!-- 現在の勤務状態 --&gt;
      &lt;div class="mt-6 p-4 bg-gray-50 rounded-lg"&gt;
        &lt;div class="flex items-center justify-between"&gt;
          &lt;div&gt;
            &lt;span class="text-sm text-gray-600"&gt;現在の状態:&lt;/span&gt;
            &lt;span :class="isWorking ? 'text-green-600' : 'text-gray-600'" class="ml-2 font-semibold"&gt;
              {{ isWorking ? '勤務中' : '勤務時間外' }}
            &lt;/span&gt;
          &lt;/div&gt;
          &lt;div v-if="todayStartTime"&gt;
            &lt;span class="text-sm text-gray-600"&gt;出勤時刻:&lt;/span&gt;
            &lt;span class="ml-2 font-semibold"&gt;{{ todayStartTime }}&lt;/span&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;!-- クイックアクション --&gt;
    &lt;div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8"&gt;
      &lt;nuxt-link
        to="/attendance"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-blue-500"
      &gt;
        &lt;div class="flex items-center"&gt;
          &lt;i class="fas fa-calendar-alt text-blue-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;div&gt;
            &lt;h3 class="text-lg font-semibold text-gray-800"&gt;勤怠一覧&lt;/h3&gt;
            &lt;p class="text-gray-600 text-sm"&gt;過去の勤怠記録を確認&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/correction-requests"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-yellow-500"
      &gt;
        &lt;div class="flex items-center"&gt;
          &lt;i class="fas fa-edit text-yellow-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;div&gt;
            &lt;h3 class="text-lg font-semibold text-gray-800"&gt;修正申請&lt;/h3&gt;
            &lt;p class="text-gray-600 text-sm"&gt;勤怠の修正を申請&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/nuxt-link&gt;
      
      &lt;nuxt-link
        to="/my-requests"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-green-500"
      &gt;
        &lt;div class="flex items-center"&gt;
          &lt;i class="fas fa-list-alt text-green-500 text-2xl mr-4"&gt;&lt;/i&gt;
          &lt;div&gt;
            &lt;h3 class="text-lg font-semibold text-gray-800"&gt;申請状況&lt;/h3&gt;
            &lt;p class="text-gray-600 text-sm"&gt;提出した申請の確認&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/nuxt-link&gt;
    &lt;/div&gt;
    
    &lt;!-- 今月の勤怠サマリー --&gt;
    &lt;div class="bg-white rounded-lg shadow-md p-6"&gt;
      &lt;h2 class="text-xl font-semibold text-gray-800 mb-4"&gt;今月の勤怠サマリー&lt;/h2&gt;
      &lt;div class="grid grid-cols-1 md:grid-cols-4 gap-4"&gt;
        &lt;div class="text-center p-4 bg-blue-50 rounded-lg"&gt;
          &lt;div class="text-2xl font-bold text-blue-600"&gt;{{ monthlyStats.workDays }}&lt;/div&gt;
          &lt;div class="text-sm text-gray-600"&gt;出勤日数&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="text-center p-4 bg-green-50 rounded-lg"&gt;
          &lt;div class="text-2xl font-bold text-green-600"&gt;{{ monthlyStats.totalHours }}&lt;/div&gt;
          &lt;div class="text-sm text-gray-600"&gt;総労働時間&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="text-center p-4 bg-yellow-50 rounded-lg"&gt;
          &lt;div class="text-2xl font-bold text-yellow-600"&gt;{{ monthlyStats.averageHours }}&lt;/div&gt;
          &lt;div class="text-sm text-gray-600"&gt;平均労働時間&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="text-center p-4 bg-red-50 rounded-lg"&gt;
          &lt;div class="text-2xl font-bold text-red-600"&gt;{{ monthlyStats.pendingRequests }}&lt;/div&gt;
          &lt;div class="text-sm text-gray-600"&gt;承認待ち申請&lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
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
        await new Promise(resolve =&gt; setTimeout(resolve, 500))
        
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
        await new Promise(resolve =&gt; setTimeout(resolve, 500))
        
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
        await new Promise(resolve =&gt; setTimeout(resolve, 300))
        
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
&lt;/script&gt;