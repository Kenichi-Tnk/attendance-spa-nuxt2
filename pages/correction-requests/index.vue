<template>
  <div class="correction-requests">
    <div class="correction-requests__page-header">
      <h1 class="correction-requests__title">
        修正申請一覧
      </h1>
      <p class="correction-requests__subtitle">
        提出した修正申請の状況を確認できます
      </p>
    </div>

    <!-- ステータスフィルター -->
    <div class="correction-requests__filter-section">
      <div class="correction-requests__filter-tabs">
        <button
          v-for="filter in statusFilters"
          :key="filter.value"
          :class="[
            'correction-requests__filter-tab',
            { 'correction-requests__filter-tab--active': selectedStatus === filter.value }
          ]"
          @click="selectedStatus = filter.value"
        >
          <i :class="filter.icon" />
          {{ filter.label }}
          <span v-if="filter.count !== undefined" class="correction-requests__filter-count">
            {{ filter.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- 申請一覧テーブル -->
    <div class="correction-requests__table-container">
      <AttendanceTable
        title="申請一覧"
        :data="filteredRequests"
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
            <i class="fas fa-clipboard-list" />
            <p>修正申請はありません</p>
            <p class="correction-requests__empty-hint">
              修正申請は勤怠一覧から行えます
            </p>
            <nuxt-link
              to="/attendance"
              class="correction-requests__empty-link"
            >
              <i class="fas fa-calendar-alt" />
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

  data () {
    return {
      correctionRequests: [],
      currentPage: 1,
      totalPages: 1,
      isLoading: false,
      selectedStatus: 'all',
      statusCounts: {
        all: 0,
        pending: 0,
        approved: 0,
        rejected: 0
      }
    }
  },

  computed: {
    tableColumns () {
      return [
        { key: 'date', label: '対象日', type: 'date' },
        { key: 'reason', label: '申請理由', type: 'text' },
        { key: 'status', label: 'ステータス', type: 'status' },
        { key: 'created_at', label: '申請日時', type: 'datetime' }
      ]
    },

    statusFilters () {
      return [
        { value: 'all', label: '全て', icon: 'fas fa-list', count: this.statusCounts.all },
        { value: 'pending', label: '承認待ち', icon: 'fas fa-hourglass-half', count: this.statusCounts.pending },
        { value: 'approved', label: '承認済み', icon: 'fas fa-check-circle', count: this.statusCounts.approved },
        { value: 'rejected', label: '却下', icon: 'fas fa-times-circle', count: this.statusCounts.rejected }
      ]
    },

    filteredRequests () {
      if (this.selectedStatus === 'all') {
        return this.correctionRequests
      }
      return this.correctionRequests.filter(request => request.statusValue === this.selectedStatus)
    }
  },

  watch: {
    selectedStatus () {
      // ステータス変更時はページを1に戻す
      this.currentPage = 1
    }
  },

  mounted () {
    this.loadCorrectionRequests()
  },

  methods: {
    async loadCorrectionRequests () {
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
          status: this.formatStatusText(request.status),
          statusValue: request.status, // フィルタリング用に元の値も保持
          created_at: this.formatDateTime(request.created_at),
          original: request
        }))

        // ステータスごとの件数をカウント
        this.statusCounts.all = this.correctionRequests.length
        this.statusCounts.pending = this.correctionRequests.filter(r => r.statusValue === 'pending').length
        this.statusCounts.approved = this.correctionRequests.filter(r => r.statusValue === 'approved').length
        this.statusCounts.rejected = this.correctionRequests.filter(r => r.statusValue === 'rejected').length

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

    formatTime (timeString) {
      if (!timeString) { return '−' }
      return timeString.substring(0, 5)
    },

    formatDateTime (dateTimeString) {
      if (!dateTimeString) { return '−' }
      const date = new Date(dateTimeString)
      return date.toLocaleString('ja-JP', {
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    formatStatusText (status) {
      const statusMap = {
        pending: '承認待ち',
        approved: '承認済み',
        rejected: '却下'
      }
      return statusMap[status] || status
    },

    goToDetail (item) {
      this.$router.push(`/correction-requests/${item.id}`)
    },

    handlePageChange (page) {
      this.currentPage = page
      this.loadCorrectionRequests()
    }
  }
}
</script>

<style src="@/assets/css/pages/correction-requests-index.css" scoped></style>
