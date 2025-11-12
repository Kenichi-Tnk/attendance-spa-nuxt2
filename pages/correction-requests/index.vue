<template>
  <div class="correction-requests">
    <PageHeader
      title="修正申請"
      :breadcrumbs="breadcrumbs"
    />
    
    <!-- 新規申請ボタン -->
    <div class="correction-requests__actions">
      <nuxt-link
        to="/correction-requests/new"
        class="correction-requests__new-btn"
      >
        <i class="fas fa-plus"></i>
        新規申請作成
      </nuxt-link>
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
            <p>申請はありません</p>
            <nuxt-link
              to="/correction-requests/new"
              class="correction-requests__empty-link"
            >
              新規申請を作成する
            </nuxt-link>
          </div>
        </template>
      </AttendanceTable>
    </div>
  </div>
</template>

<script>
import AttendanceTable from '~/components/AttendanceTable.vue'
import PageHeader from '~/components/PageHeader.vue'
import StatusBadge from '~/components/StatusBadge.vue'

export default {
  name: 'CorrectionRequestsList',
  components: {
    AttendanceTable,
    PageHeader,
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
    breadcrumbs() {
      return [
        { text: 'ダッシュボード', to: '/dashboard' },
        { text: '修正申請' }
      ]
    },
    
    tableColumns() {
      return [
        { key: 'date', label: '対象日', type: 'date' },
        { key: 'check_in', label: '修正後出勤時刻', type: 'time' },
        { key: 'check_out', label: '修正後退勤時刻', type: 'time' },
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
          check_in: this.formatTime(request.check_in),
          check_out: this.formatTime(request.check_out),
          reason: request.reason.length > 50 ? request.reason.substring(0, 50) + '...' : request.reason,
          status: request.status,
          created_at: this.formatDateTime(request.created_at),
          original: request // 元データも保持
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
      // HH:MM:SS形式をHH:MM形式に変換
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

<style scoped>
.correction-requests {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.correction-requests__actions {
  margin-bottom: 20px;
  display: flex;
  justify-content: flex-end;
}

.correction-requests__new-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background-color: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 500;
  transition: background-color 0.2s;
}

.correction-requests__new-btn:hover {
  background-color: #2563eb;
}

.correction-requests__table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.correction-requests__empty {
  text-align: center;
  padding: 40px 20px;
}

.correction-requests__empty i {
  font-size: 48px;
  color: #9ca3af;
  margin-bottom: 16px;
}

.correction-requests__empty p {
  color: #6b7280;
  margin-bottom: 16px;
  font-size: 16px;
}

.correction-requests__empty-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background-color: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-size: 14px;
  transition: background-color 0.2s;
}

.correction-requests__empty-link:hover {
  background-color: #2563eb;
}
</style>