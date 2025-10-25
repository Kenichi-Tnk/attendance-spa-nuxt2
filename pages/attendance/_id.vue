<template>
  <div class="container mx-auto px-4 py-8">
    <div class="mb-8">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
        <nuxt-link to="/attendance" class="hover:text-blue-600">勤怠一覧</nuxt-link>
        <span>/</span>
        <span class="text-gray-900">勤怠詳細</span>
      </nav>
      
      <h1 class="text-3xl font-bold text-gray-900">勤怠詳細</h1>
      <p class="text-gray-600 mt-2">{{ formatFullDate(attendance.date) }}の勤怠記録</p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- 勤怠情報 -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">勤怠情報</h2>
        
        <div class="space-y-4">
          <div class="flex justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">日付</span>
            <span class="font-semibold">{{ formatFullDate(attendance.date) }}</span>
          </div>
          
          <div class="flex justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">出勤時刻</span>
            <span :class="attendance.clockInLate ? 'text-red-600 font-semibold' : 'font-semibold'">
              {{ attendance.clockIn || '−' }}
              <i v-if="attendance.clockInLate" class="fas fa-exclamation-triangle ml-1"></i>
            </span>
          </div>
          
          <div class="flex justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">退勤時刻</span>
            <span :class="attendance.clockOutEarly ? 'text-orange-600 font-semibold' : 'font-semibold'">
              {{ attendance.clockOut || '−' }}
              <i v-if="attendance.clockOutEarly" class="fas fa-exclamation-triangle ml-1"></i>
            </span>
          </div>
          
          <div class="flex justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">休憩時間</span>
            <span class="font-semibold">{{ attendance.breakTime || '1:00' }}</span>
          </div>
          
          <div class="flex justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">実労働時間</span>
            <span class="font-semibold text-lg text-blue-600">{{ attendance.workHours || '−' }}</span>
          </div>
          
          <div class="flex justify-between py-3">
            <span class="text-gray-600">ステータス</span>
            <span :class="getStatusClass(attendance.status)" class="px-3 py-1 rounded-full text-sm font-medium">
              {{ getStatusText(attendance.status) }}
            </span>
          </div>
        </div>
        
        <!-- 操作ボタン -->
        <div class="mt-6 flex space-x-3">
          <button
            v-if="canRequestCorrection"
            @click="requestCorrection"
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            <i class="fas fa-edit mr-2"></i>
            修正申請
          </button>
          
          <nuxt-link
            to="/attendance"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            <i class="fas fa-arrow-left mr-2"></i>
            一覧に戻る
          </nuxt-link>
        </div>
      </div>
      
      <!-- 修正履歴 -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">修正履歴</h2>
        
        <div v-if="correctionHistory.length === 0" class="text-center py-8 text-gray-500">
          <i class="fas fa-history text-4xl mb-4"></i>
          <p>修正履歴はありません</p>
        </div>
        
        <div v-else class="space-y-4">
          <div v-for="correction in correctionHistory" :key="correction.id" class="border-l-4 border-blue-500 pl-4 py-3 bg-gray-50 rounded-r-lg">
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-semibold text-gray-800">{{ correction.type }}</h4>
              <span :class="getCorrectionStatusClass(correction.status)" class="px-2 py-1 rounded text-xs font-medium">
                {{ getCorrectionStatusText(correction.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-2">{{ correction.reason }}</p>
            <div class="text-xs text-gray-500">
              <span>申請日時: {{ formatDateTime(correction.requestedAt) }}</span>
              <span v-if="correction.reviewedAt" class="ml-4">承認日時: {{ formatDateTime(correction.reviewedAt) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
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
          type: '出勤時刻修正',
          reason: '電車遅延のため',
          status: 'approved',
          requestedAt: '2024-10-26T09:30:00',
          reviewedAt: '2024-10-26T14:00:00'
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
        await new Promise(resolve => setTimeout(resolve, 500))
        
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
</script>
