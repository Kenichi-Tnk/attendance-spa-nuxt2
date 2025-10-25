&lt;template&gt;
  &lt;div class="container mx-auto px-4 py-8"&gt;
    &lt;div class="mb-8"&gt;
      &lt;h1 class="text-3xl font-bold text-gray-900"&gt;勤怠一覧&lt;/h1&gt;
      &lt;p class="text-gray-600 mt-2"&gt;過去の勤怠記録を確認できます&lt;/p&gt;
    &lt;/div&gt;
    
    &lt;!-- フィルター --&gt;
    &lt;div class="bg-white rounded-lg shadow-md p-6 mb-6"&gt;
      &lt;h2 class="text-lg font-semibold text-gray-800 mb-4"&gt;絞り込み&lt;/h2&gt;
      &lt;div class="grid grid-cols-1 md:grid-cols-3 gap-4"&gt;
        &lt;div&gt;
          &lt;label class="block text-sm font-medium text-gray-700 mb-2"&gt;年月&lt;/label&gt;
          &lt;input
            v-model="selectedMonth"
            type="month"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          &gt;
        &lt;/div&gt;
        &lt;div class="flex items-end"&gt;
          &lt;button
            @click="loadAttendanceData"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors"
          &gt;
            &lt;i class="fas fa-search mr-2"&gt;&lt;/i&gt;
            検索
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;!-- 勤怠記録テーブル --&gt;
    &lt;div class="bg-white rounded-lg shadow-md overflow-hidden"&gt;
      &lt;div class="px-6 py-4 border-b border-gray-200"&gt;
        &lt;h2 class="text-lg font-semibold text-gray-800"&gt;{{ formatMonthYear(selectedMonth) }}の勤怠記録&lt;/h2&gt;
      &lt;/div&gt;
      
      &lt;div class="overflow-x-auto"&gt;
        &lt;table class="min-w-full divide-y divide-gray-200"&gt;
          &lt;thead class="bg-gray-50"&gt;
            &lt;tr&gt;
              &lt;th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"&gt;
                日付
              &lt;/th&gt;
              &lt;th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"&gt;
                出勤時刻
              &lt;/th&gt;
              &lt;th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"&gt;
                退勤時刻
              &lt;/th&gt;
              &lt;th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"&gt;
                労働時間
              &lt;/th&gt;
              &lt;th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"&gt;
                ステータス
              &lt;/th&gt;
              &lt;th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"&gt;
                操作
              &lt;/th&gt;
            &lt;/tr&gt;
          &lt;/thead&gt;
          &lt;tbody class="bg-white divide-y divide-gray-200"&gt;
            &lt;tr v-for="record in attendanceRecords" :key="record.id" class="hover:bg-gray-50"&gt;
              &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"&gt;
                {{ formatDate(record.date) }}
                &lt;span class="block text-xs text-gray-500"&gt;{{ getDayOfWeek(record.date) }}&lt;/span&gt;
              &lt;/td&gt;
              &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"&gt;
                {{ record.clockIn || '−' }}
              &lt;/td&gt;
              &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"&gt;
                {{ record.clockOut || '−' }}
              &lt;/td&gt;
              &lt;td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"&gt;
                {{ record.workHours || '−' }}
              &lt;/td&gt;
              &lt;td class="px-6 py-4 whitespace-nowrap"&gt;
                &lt;span :class="getStatusClass(record.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"&gt;
                  {{ getStatusText(record.status) }}
                &lt;/span&gt;
              &lt;/td&gt;
              &lt;td class="px-6 py-4 whitespace-nowrap text-sm font-medium"&gt;
                &lt;nuxt-link
                  :to="`/attendance/${record.id}`"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                &gt;
                  詳細
                &lt;/nuxt-link&gt;
                &lt;button
                  v-if="canRequestCorrection(record)"
                  @click="requestCorrection(record.id)"
                  class="text-yellow-600 hover:text-yellow-900"
                &gt;
                  修正申請
                &lt;/button&gt;
              &lt;/td&gt;
            &lt;/tr&gt;
          &lt;/tbody&gt;
        &lt;/table&gt;
      &lt;/div&gt;
      
      &lt;!-- ページネーション --&gt;
      &lt;div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between"&gt;
        &lt;div class="text-sm text-gray-700"&gt;
          {{ attendanceRecords.length }}件の記録を表示中
        &lt;/div&gt;
        &lt;div class="flex space-x-2"&gt;
          &lt;button
            :disabled="currentPage === 1"
            @click="currentPage--"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
          &gt;
            前へ
          &lt;/button&gt;
          &lt;span class="px-3 py-1 text-sm text-gray-700"&gt;
            {{ currentPage }} / {{ totalPages }}
          &lt;/span&gt;
          &lt;button
            :disabled="currentPage === totalPages"
            @click="currentPage++"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
          &gt;
            次へ
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
  name: 'AttendanceList',
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      selectedMonth: new Date().toISOString().slice(0, 7), // YYYY-MM形式
      currentPage: 1,
      totalPages: 1,
      attendanceRecords: [
        {
          id: 1,
          date: '2024-10-25',
          clockIn: '09:00',
          clockOut: '18:00',
          workHours: '8:00',
          status: 'normal'
        },
        {
          id: 2,
          date: '2024-10-24',
          clockIn: '09:15',
          clockOut: '18:30',
          workHours: '8:15',
          status: 'late'
        },
        {
          id: 3,
          date: '2024-10-23',
          clockIn: '09:00',
          clockOut: '17:45',
          workHours: '7:45',
          status: 'early_leave'
        },
        {
          id: 4,
          date: '2024-10-22',
          clockIn: '09:00',
          clockOut: '18:00',
          workHours: '8:00',
          status: 'normal'
        },
        {
          id: 5,
          date: '2024-10-21',
          clockIn: null,
          clockOut: null,
          workHours: null,
          status: 'absent'
        }
      ]
    }
  },
  
  mounted() {
    this.loadAttendanceData()
  },
  
  methods: {
    async loadAttendanceData() {
      try {
        // TODO: API呼び出しで勤怠データを取得
        await new Promise(resolve =&gt; setTimeout(resolve, 500))
        
        // モックデータはすでにdata()で設定済み
        this.totalPages = Math.ceil(this.attendanceRecords.length / 10)
      } catch (error) {
        console.error('勤怠データ取得エラー:', error)
        this.$toast.error('勤怠データの取得に失敗しました')
      }
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        month: 'numeric',
        day: 'numeric'
      })
    },
    
    formatMonthYear(monthString) {
      const date = new Date(monthString + '-01')
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long'
      })
    },
    
    getDayOfWeek(dateString) {
      const date = new Date(dateString)
      const days = ['日', '月', '火', '水', '木', '金', '土']
      return days[date.getDay()]
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
    
    canRequestCorrection(record) {
      // 修正申請可能な条件をチェック
      return record.status !== 'pending' && record.clockIn
    },
    
    requestCorrection(recordId) {
      // 修正申請ページへ遷移
      this.$router.push(`/correction-requests/new?attendance_id=${recordId}`)
    }
  }
}
&lt;/script&gt;