&lt;template&gt;
  &lt;div class="container mx-auto px-4 py-8"&gt;
    &lt;PageHeader
      title="勤怠一覧"
      subtitle="過去の勤怠記録を確認できます"
      icon="fas fa-calendar-alt"
      :breadcrumbs="breadcrumbs"
    /&gt;
    
    &lt;!-- フィルター --&gt;
    &lt;div class="bg-white rounded-lg shadow-md p-6 mb-6"&gt;
      &lt;h2 class="text-lg font-semibold text-gray-800 mb-4"&gt;絞り込み&lt;/h2&gt;
      &lt;div class="grid grid-cols-1 md:grid-cols-3 gap-4"&gt;
        &lt;FormInput
          v-model="selectedMonth"
          type="month"
          label="年月"
          :required="false"
        /&gt;
        &lt;div class="flex items-end"&gt;
          &lt;button
            @click="loadAttendanceData"
            :disabled="isLoading"
            class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-md transition-colors"
          &gt;
            &lt;i class="fas fa-search mr-2"&gt;&lt;/i&gt;
            検索
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;!-- 勤怠記録テーブル --&gt;
    &lt;AttendanceTable
      :title="`${formatMonthYear(selectedMonth)}の勤怠記録`"
      :data="attendanceRecords"
      :columns="tableColumns"
      :loading="isLoading"
      :current-page="currentPage"
      :total-pages="totalPages"
      :total-items="totalItems"
      @detail="goToDetail"
      @page-change="handlePageChange"
    &gt;
      &lt;template #cell-date="{ item }"&gt;
        &lt;div&gt;
          &lt;div class="text-gray-900"&gt;{{ formatDate(item.date) }}&lt;/div&gt;
          &lt;div class="text-xs text-gray-500"&gt;{{ getDayOfWeek(item.date) }}&lt;/div&gt;
        &lt;/div&gt;
      &lt;/template&gt;
      
      &lt;template #actions="{ item }"&gt;
        &lt;nuxt-link
          :to="`/attendance/${item.id}`"
          class="text-blue-600 hover:text-blue-900 mr-3"
        &gt;
          詳細
        &lt;/nuxt-link&gt;
        &lt;button
          v-if="canRequestCorrection(item)"
          @click="requestCorrection(item.id)"
          class="text-yellow-600 hover:text-yellow-900"
        &gt;
          修正申請
        &lt;/button&gt;
      &lt;/template&gt;
    &lt;/AttendanceTable&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import AttendanceTable from '~/components/AttendanceTable.vue'
import PageHeader from '~/components/PageHeader.vue'
import FormInput from '~/components/FormInput.vue'

export default {
  name: 'AttendanceList',
  components: {
    AttendanceTable,
    PageHeader,
    FormInput
  },
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      selectedMonth: new Date().toISOString().slice(0, 7), // YYYY-MM形式
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      isLoading: false,
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
  
  computed: {
    breadcrumbs() {
      return [
        { text: 'ダッシュボード', to: '/dashboard' },
        { text: '勤怠一覧' }
      ]
    },
    
    tableColumns() {
      return [
        { key: 'date', label: '日付', type: 'date' },
        { key: 'clockIn', label: '出勤時刻', type: 'time' },
        { key: 'clockOut', label: '退勤時刻', type: 'time' },
        { key: 'workHours', label: '労働時間', type: 'time' },
        { key: 'status', label: 'ステータス', type: 'status' }
      ]
    }
  },
  
  mounted() {
    this.loadAttendanceData()
  },
  
  methods: {
    async loadAttendanceData() {
      try {
        this.isLoading = true
        
        // TODO: API呼び出しで勤怠データを取得
        await new Promise(resolve =&gt; setTimeout(resolve, 500))
        
        // モックデータはすでにdata()で設定済み
        this.totalItems = this.attendanceRecords.length
        this.totalPages = Math.ceil(this.totalItems / 10)
      } catch (error) {
        console.error('勤怠データ取得エラー:', error)
        this.$toast.error('勤怠データの取得に失敗しました')
      } finally {
        this.isLoading = false
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
    
    canRequestCorrection(record) {
      // 修正申請可能な条件をチェック
      return record.status !== 'pending' &amp;&amp; record.clockIn
    },
    
    goToDetail(item) {
      this.$router.push(`/attendance/${item.id}`)
    },
    
    requestCorrection(recordId) {
      // 修正申請ページへ遷移
      this.$router.push(`/correction-requests/new?attendance_id=${recordId}`)
    },
    
    handlePageChange(page) {
      this.currentPage = page
      this.loadAttendanceData()
    }
  }
}
&lt;/script&gt;