<template>
  <div class="correction-requests">
    <div class="correction-requests__page-header">
      <h1 class="correction-requests__title">修正申請一覧</h1>
      <p class="correction-requests__subtitle">提出した修正申請の状況を確認できます</p>
    </div>

    <!-- 申請一覧テーブル -->
    <div class="correction-requests__table-container">
      <AttendanceTable
        title="申請一覧"
        :data="correctionRequests"
        :columns="tableColumns"
        :loading="isLoading"
        :show-actions="true"
        :show-detail-action="true"
        :show-edit-action="false"
        :show-delete-action="false"
        :show-pagination="true"
        :current-page="currentPage"
        :total-pages="totalPages"
        @detail="goToDetail"
        @page-change="handlePageChange"
      >
        <!-- ステータス列のカスタム表示 -->
        <template #cell-status="{ value }">
          <StatusBadge :status="value" />
        </template>

        <!-- 空データ時の表示 -->
        <template #empty>
          <div class="correction-requests__empty">
            <i class="fas fa-clipboard-list"></i>
            <p>修正申請はありません</p>
            <p class="correction-requests__empty-hint">
              修正申請は勤怠一覧から行えます
            </p>
            <nuxt-link
              to="/attendance"
              class="correction-requests__empty-link"
            >
              <i class="fas fa-calendar-alt"></i>
              勤怠一覧へ
            </nuxt-link>
          </div>
        </template>
      </AttendanceTable>
    </div>
  </div>
</template>

<script>
import AttendanceTable from '~/components/AttendanceTable.vue'
import StatusBadge from '~/components/StatusBadge.vue'

export default {
  name: 'CorrectionRequestsList',
  components: {
    AttendanceTable,
    StatusBadge
  },
  middleware: ['auth', 'verified'],
  
  data() {
    return {
      correctionRequests: [],
      currentPage: 1,
      totalPages: 1,
      isLoading: false
    }
  },
  
  computed: {
    tableColumns() {
      return [
        { key: 'date', label: '対象日', type: 'date' },
        { key: 'reason', label: '申請理由', type: 'text' },
        { key: 'status', label: 'ステータス', type: 'status' },
        { key: 'created_at', label: '申請日時', type: 'datetime' }
      ]
    }
  },
  
  mounted() {
    this.loadCorrectionRequests()
  },
  
  methods: {
    async loadCorrectionRequests() {
      try {
        this.isLoading = true
        
        const params = {
          page: this.currentPage
        }
        
        const response = await this.$axios.$get('/api/correction-requests', { params })
        
        // データを画面表示用にフォーマット
        this.correctionRequests = response.data.map(request => ({
          id: request.id,
          date: request.date,
          reason: request.reason.length > 50 ? request.reason.substring(0, 50) + '...' : request.reason,
          status: request.status,
          created_at: this.formatDateTime(request.created_at),
          original: request
        }))
        
        this.totalPages = response.last_page || 1
      } catch (error) {
        console.error('申請一覧取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('申請一覧の取得に失敗しました')
        }
      } finally {
        this.isLoading = false
      }
    },
    
    formatTime(timeString) {
      if (!timeString) return '−'
      return timeString.substring(0, 5)
    },
    
    formatDateTime(dateTimeString) {
      if (!dateTimeString) return '−'
      const date = new Date(dateTimeString)
      return date.toLocaleString('ja-JP', {
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    goToDetail(item) {
      this.$router.push(`/correction-requests/${item.id}`)
    },
    
    handlePageChange(page) {
      this.currentPage = page
      this.loadCorrectionRequests()
    }
  }
}
</script>

<style src="@/assets/css/pages/correction-requests-index.css" scoped></style>