<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">
        ダッシュボード
      </h1>
      <p class="mt-1 text-sm text-gray-600">
        {{ $store.getters['auth/user']?.name }}さん、お疲れ様です
      </p>
    </div>

    <!-- 勤務状況カード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <!-- 出勤・退勤ボタン -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  勤務状況
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ workStatus }}
                </dd>
              </dl>
            </div>
          </div>
          <div class="mt-5">
            <button
              @click="toggleWork"
              :class="workButtonClass"
              class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
            >
              {{ workButtonText }}
            </button>
          </div>
        </div>
      </div>

      <!-- 本日の勤務時間 -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  本日の勤務時間
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ todayWorkHours }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- 今月の勤務日数 -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  今月の勤務日数
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ monthlyWorkDays }}日
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 最近の勤怠履歴 -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          最近の勤怠履歴
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
          直近5日間の勤怠記録
        </p>
      </div>
      <ul class="divide-y divide-gray-200">
        <li v-for="record in recentAttendance" :key="record.id" class="px-4 py-4 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="text-sm font-medium text-gray-900">
                {{ record.date }}
              </div>
            </div>
            <div class="flex items-center space-x-4 text-sm text-gray-500">
              <span>出勤: {{ record.clock_in || '-' }}</span>
              <span>退勤: {{ record.clock_out || '-' }}</span>
              <span>勤務時間: {{ record.work_hours || '-' }}</span>
            </div>
          </div>
        </li>
      </ul>
      <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
        <NuxtLink
          to="/attendance"
          class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
        >
          すべての勤怠履歴を見る →
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  middleware: 'auth',
  
  data() {
    return {
      isWorking: false,
      currentWorkStartTime: null
    }
  },
  
  computed: {
    workStatus() {
      return this.isWorking ? '勤務中' : '退勤中'
    },
    workButtonText() {
      return this.isWorking ? '退勤する' : '出勤する'
    },
    workButtonClass() {
      return this.isWorking
        ? 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500'
        : 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
    },
    todayWorkHours() {
      // モックデータ（後でAPI連携）
      return '7時間30分'
    },
    monthlyWorkDays() {
      // モックデータ（後でAPI連携）
      return 22
    },
    recentAttendance() {
      // モックデータ（後でAPI連携）
      return [
        { id: 1, date: '2024-01-25', clock_in: '09:00', clock_out: '18:00', work_hours: '8時間00分' },
        { id: 2, date: '2024-01-24', clock_in: '09:15', clock_out: '18:30', work_hours: '8時間15分' },
        { id: 3, date: '2024-01-23', clock_in: '08:45', clock_out: '17:45', work_hours: '8時間00分' },
        { id: 4, date: '2024-01-22', clock_in: '09:00', clock_out: '18:00', work_hours: '8時間00分' },
        { id: 5, date: '2024-01-21', clock_in: '09:30', clock_out: '18:15', work_hours: '7時間45分' }
      ]
    }
  },
  
  methods: {
    toggleWork() {
      if (this.isWorking) {
        // 退勤処理（モック）
        this.clockOut()
      } else {
        // 出勤処理（モック）
        this.clockIn()
      }
    },
    
    clockIn() {
      // TODO: 後でAPI連携
      this.isWorking = true
      this.currentWorkStartTime = new Date()
      alert('出勤しました')
    },
    
    clockOut() {
      // TODO: 後でAPI連携
      this.isWorking = false
      this.currentWorkStartTime = null
      alert('退勤しました')
    }
  }
}
</script>