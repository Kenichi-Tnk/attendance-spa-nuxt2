&lt;template&gt;
  &lt;div class="container mx-auto px-4 py-8"&gt;
    &lt;div class="mb-8"&gt;
      &lt;nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4"&gt;
        &lt;nuxt-link to="/attendance" class="hover:text-blue-600"&gt;勤怠一覧&lt;/nuxt-link&gt;
        &lt;span&gt;/&lt;/span&gt;
        &lt;span class="text-gray-900"&gt;勤怠詳細&lt;/span&gt;
      &lt;/nav&gt;
      
      &lt;h1 class="text-3xl font-bold text-gray-900"&gt;勤怠詳細&lt;/h1&gt;
      &lt;p class="text-gray-600 mt-2"&gt;{{ formatFullDate(attendance.date) }}の勤怠記録&lt;/p&gt;
    &lt;/div&gt;
    
    &lt;div class="grid grid-cols-1 lg:grid-cols-2 gap-8"&gt;
      &lt;!-- 勤怠情報 --&gt;
      &lt;div class="bg-white rounded-lg shadow-md p-6"&gt;
        &lt;h2 class="text-xl font-semibold text-gray-800 mb-6"&gt;勤怠情報&lt;/h2&gt;
        
        &lt;div class="space-y-4"&gt;
          &lt;div class="flex justify-between py-3 border-b border-gray-100"&gt;
            &lt;span class="text-gray-600"&gt;日付&lt;/span&gt;
            &lt;span class="font-semibold"&gt;{{ formatFullDate(attendance.date) }}&lt;/span&gt;
          &lt;/div&gt;
          
          &lt;div class="flex justify-between py-3 border-b border-gray-100"&gt;
            &lt;span class="text-gray-600"&gt;出勤時刻&lt;/span&gt;
            &lt;span class="font-semibold"&gt;
              {{ attendance.clockIn || '−' }}
              &lt;span v-if="attendance.clockInLate" class="text-red-500 text-sm ml-2"&gt;(遅刻)&lt;/span&gt;
            &lt;/span&gt;
          &lt;/div&gt;
          
          &lt;div class="flex justify-between py-3 border-b border-gray-100"&gt;
            &lt;span class="text-gray-600"&gt;退勤時刻&lt;/span&gt;
            &lt;span class="font-semibold"&gt;
              {{ attendance.clockOut || '−' }}
              &lt;span v-if="attendance.clockOutEarly" class="text-orange-500 text-sm ml-2"&gt;(早退)&lt;/span&gt;
            &lt;/span&gt;
          &lt;/div&gt;
          
          &lt;div class="flex justify-between py-3 border-b border-gray-100"&gt;
            &lt;span class="text-gray-600"&gt;休憩時間&lt;/span&gt;
            &lt;span class="font-semibold"&gt;{{ attendance.breakTime || '1:00' }}&lt;/span&gt;
          &lt;/div&gt;
          
          &lt;div class="flex justify-between py-3 border-b border-gray-100"&gt;
            &lt;span class="text-gray-600"&gt;実労働時間&lt;/span&gt;
            &lt;span class="font-semibold text-lg text-blue-600"&gt;{{ attendance.workHours || '−' }}&lt;/span&gt;
          &lt;/div&gt;
          
          &lt;div class="flex justify-between py-3"&gt;
            &lt;span class="text-gray-600"&gt;ステータス&lt;/span&gt;
            &lt;span :class="getStatusClass(attendance.status)" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"&gt;
              {{ getStatusText(attendance.status) }}
            &lt;/span&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;!-- 操作ボタン --&gt;
        &lt;div class="mt-6 flex space-x-3"&gt;
          &lt;button
            v-if="canRequestCorrection"
            @click="requestCorrection"
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md transition-colors"
          &gt;
            &lt;i class="fas fa-edit mr-2"&gt;&lt;/i&gt;
            修正申請
          &lt;/button&gt;
          
          &lt;nuxt-link
            to="/attendance"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          &gt;
            &lt;i class="fas fa-arrow-left mr-2"&gt;&lt;/i&gt;
            一覧に戻る
          &lt;/nuxt-link&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;!-- 修正履歴 --&gt;
      &lt;div class="bg-white rounded-lg shadow-md p-6"&gt;
        &lt;h2 class="text-xl font-semibold text-gray-800 mb-6"&gt;修正履歴&lt;/h2&gt;
        
        &lt;div v-if="correctionHistory.length === 0" class="text-center py-8 text-gray-500"&gt;
          &lt;i class="fas fa-history text-4xl mb-4"&gt;&lt;/i&gt;
          &lt;p&gt;修正履歴はありません&lt;/p&gt;
        &lt;/div&gt;
        
        &lt;div v-else class="space-y-4"&gt;
          &lt;div v-for="history in correctionHistory" :key="history.id" class="border border-gray-200 rounded-lg p-4"&gt;
            &lt;div class="flex items-center justify-between mb-2"&gt;
              &lt;span :class="getCorrectionStatusClass(history.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"&gt;
                {{ getCorrectionStatusText(history.status) }}
              &lt;/span&gt;
              &lt;span class="text-sm text-gray-500"&gt;{{ formatDateTime(history.createdAt) }}&lt;/span&gt;
            &lt;/div&gt;
            
            &lt;div class="text-sm text-gray-700"&gt;
              &lt;p&gt;&lt;strong&gt;申請内容:&lt;/strong&gt; {{ history.reason }}&lt;/p&gt;
              &lt;p class="mt-1"&gt;&lt;strong&gt;修正前:&lt;/strong&gt; {{ history.beforeTime }}&lt;/p&gt;
              &lt;p&gt;&lt;strong&gt;修正後:&lt;/strong&gt; {{ history.afterTime }}&lt;/p&gt;
              
              &lt;div v-if="history.adminNote" class="mt-2 p-2 bg-gray-50 rounded"&gt;
                &lt;p&gt;&lt;strong&gt;管理者コメント:&lt;/strong&gt; {{ history.adminNote }}&lt;/p&gt;
                &lt;p class="text-xs text-gray-500 mt-1"&gt;承認者: {{ history.approvedBy }}&lt;/p&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
  name: 'AttendanceDetail',
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      attendance: {
        id: 1,
        date: '2024-10-25',
        clockIn: '09:15',
        clockOut: '18:30',
        workHours: '8:15',
        breakTime: '1:00',
        status: 'late',
        clockInLate: true,
        clockOutEarly: false
      },
      correctionHistory: [
        {
          id: 1,
          status: 'approved',
          reason: '電車遅延のため遅刻しました',
          beforeTime: '09:15',
          afterTime: '09:00',
          adminNote: '電車遅延証明書を確認しました。承認いたします。',
          approvedBy: '管理者',
          createdAt: '2024-10-25T10:30:00Z'
        }
      ]
    }
  },
  
  computed: {
    canRequestCorrection() {
      return this.attendance.status !== 'pending'
    }
  },
  
  async mounted() {
    await this.loadAttendanceDetail()
  },
  
  methods: {
    async loadAttendanceDetail() {
      try {
        const id = this.$route.params.id
        
        // TODO: API呼び出しで勤怠詳細を取得
        await new Promise(resolve =&gt; setTimeout(resolve, 500))
        
        // モックデータはすでにdata()で設定済み
      } catch (error) {
        console.error('勤怠詳細取得エラー:', error)
        this.$toast.error('勤怠詳細の取得に失敗しました')
      }
    },
    
    formatFullDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long'
      })
    },
    
    formatDateTime(dateTimeString) {
      const date = new Date(dateTimeString)
      return date.toLocaleString('ja-JP')
    },
    
    getStatusClass(status) {
      const classes = {
        normal: 'bg-green-100 text-green-800',
        late: 'bg-yellow-100 text-yellow-800',
        early_leave: 'bg-orange-100 text-orange-800',
        absent: 'bg-red-100 text-red-800',
        pending: 'bg-blue-100 text-blue-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    
    getStatusText(status) {
      const texts = {
        normal: '正常',
        late: '遅刻',
        early_leave: '早退',
        absent: '欠席',
        pending: '承認待ち'
      }
      return texts[status] || '不明'
    },
    
    getCorrectionStatusClass(status) {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    
    getCorrectionStatusText(status) {
      const texts = {
        pending: '承認待ち',
        approved: '承認済み',
        rejected: '却下'
      }
      return texts[status] || '不明'
    },
    
    requestCorrection() {
      this.$router.push(`/correction-requests/new?attendance_id=${this.attendance.id}`)
    }
  }
}
&lt;/script&gt;