<template>
  <div class="admin-corrections">
    <PageHeader
      title="修正申請管理"
      subtitle="スタッフからの修正申請を確認・承認"
      icon="fas fa-edit"
      icon-color="#EF4444"
    />

    <div class="admin-corrections__container">
      <!-- フィルターセクション -->
      <div class="admin-corrections__filters">
        <div class="admin-corrections__filter-group">
          <label class="admin-corrections__filter-label">ステータス</label>
          <select
            v-model="selectedStatus"
            class="admin-corrections__filter-select"
            @change="filterRequests"
          >
            <option value="">
              全て
            </option>
            <option value="pending">
              承認待ち
            </option>
            <option value="approved">
              承認済
            </option>
            <option value="rejected">
              却下
            </option>
          </select>
        </div>
      </div>

      <!-- 申請一覧 -->
      <div class="admin-corrections__list">
        <div v-if="isLoading" class="admin-corrections__loading">
          <LoadingSpinner />
          <p>申請データを読み込み中...</p>
        </div>

        <div v-else-if="filteredRequests.length === 0" class="admin-corrections__empty">
          <i class="fas fa-inbox" />
          <p>{{ selectedStatus ? 'フィルター条件に一致する' : '' }}申請がありません</p>
        </div>

        <div v-else class="admin-corrections__grid">
          <div
            v-for="request in filteredRequests"
            :key="request.id"
            class="admin-corrections__card"
            :class="getStatusClass(request.status)"
          >
            <!-- ヘッダー -->
            <div class="admin-corrections__card-header">
              <div class="admin-corrections__card-info">
                <h3 class="admin-corrections__card-title">
                  {{ request.user.name }}
                </h3>
                <p class="admin-corrections__card-date">
                  {{ formatDate(request.date) }}
                </p>
              </div>
              <StatusBadge :status="request.status" type="request" />
            </div>

            <!-- 修正内容 -->
            <div class="admin-corrections__card-body">
              <div class="admin-corrections__time-grid">
                <div class="admin-corrections__time-item">
                  <label>出勤時刻</label>
                  <div class="admin-corrections__time-change">
                    <span class="admin-corrections__time-corrected">{{ formatTime(request.check_in) }}</span>
                  </div>
                </div>

                <div class="admin-corrections__time-item">
                  <label>退勤時刻</label>
                  <div class="admin-corrections__time-change">
                    <span class="admin-corrections__time-corrected">{{ formatTime(request.check_out) }}</span>
                  </div>
                </div>
              </div>

              <!-- 修正理由 -->
              <div class="admin-corrections__reason">
                <label>修正理由</label>
                <p>{{ request.reason }}</p>
              </div>

              <!-- アクションボタン（承認待ちの場合のみ） -->
              <div v-if="request.status === 'pending'" class="admin-corrections__actions">
                <button
                  :disabled="processing"
                  class="admin-corrections__btn admin-corrections__btn--approve"
                  @click="approveRequest(request.id)"
                >
                  <i class="fas fa-check" />
                  承認
                </button>

                <button
                  :disabled="processing"
                  class="admin-corrections__btn admin-corrections__btn--reject"
                  @click="rejectRequest(request.id)"
                >
                  <i class="fas fa-times" />
                  却下
                </button>
              </div>

              <!-- 処理済み情報 -->
              <div v-else class="admin-corrections__processed">
                <p>
                  <i :class="request.status === 'approved' ? 'fas fa-check-circle' : 'fas fa-times-circle'" />
                  {{ request.status === 'approved' ? '承認済み' : '却下済み' }}
                  <span class="admin-corrections__processed-date">{{ formatDateTime(request.updated_at) }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminCorrectionRequests',
  middleware: ['auth', 'verified', 'admin'],

  data () {
    return {
      requests: [],
      filteredRequests: [],
      selectedStatus: '',
      isLoading: false,
      processing: false
    }
  },

  async mounted () {
    await this.fetchRequests()
  },

  methods: {
    async fetchRequests () {
      try {
        this.isLoading = true
        const response = await this.$axios.get('/api/correction-requests/admin/all', {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        // APIレスポンスから適切にデータを抽出
        let requestsData = []
        if (response.data && Array.isArray(response.data.data)) {
          requestsData = response.data.data
        } else if (response.data && Array.isArray(response.data)) {
          requestsData = response.data
        } else if (response.data && response.data.correction) {
          requestsData = [response.data.correction]
        }

        this.requests = requestsData
        this.filterRequests()
      } catch (error) {
        console.error('申請取得エラー:', error)

        let errorMessage = '申請データの取得に失敗しました'
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.message) {
          errorMessage = error.message
        }

        // Toast が利用できない場合の代替手段
        if (this.$toast && typeof this.$toast.error === 'function') {
          this.$toast.error(errorMessage)
        } else {
          alert('エラー: ' + errorMessage)
        }
      } finally {
        this.isLoading = false
      }
    },

    async approveRequest (requestId) {
      if (!confirm('この申請を承認しますか？')) { return }

      try {
        this.processing = true
        await this.$axios.put(`/api/correction-requests/${requestId}/approve`, {}, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        // Toast が利用できない場合の代替手段
        if (this.$toast && typeof this.$toast.success === 'function') {
          this.$toast.success('申請を承認しました')
        } else {
          alert('申請を承認しました')
        }

        // データを再取得して更新
        try {
          await this.fetchRequests()
        } catch (fetchError) {
          console.error('データ再取得エラー:', fetchError)
          // エラーが発生しても承認は成功しているので、ページをリロード
          window.location.reload()
        }
      } catch (error) {
        console.error('承認エラー:', error)

        let errorMessage = '承認処理に失敗しました'
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.message) {
          errorMessage = error.message
        }

        // Toast が利用できない場合の代替手段
        if (this.$toast && typeof this.$toast.error === 'function') {
          this.$toast.error(errorMessage)
        } else {
          alert('エラー: ' + errorMessage)
        }
      } finally {
        this.processing = false
      }
    },

    async rejectRequest (requestId) {
      const reason = prompt('却下理由を入力してください:')
      if (!reason || reason.trim() === '') {
        // Toast が利用できない場合の代替手段
        if (this.$toast && typeof this.$toast.error === 'function') {
          this.$toast.error('却下理由は必須です')
        } else {
          alert('却下理由は必須です')
        }
        return
      }

      if (!confirm('この申請を却下しますか？')) { return }

      try {
        this.processing = true
        await this.$axios.put(`/api/correction-requests/${requestId}/reject`, {
          reject_reason: reason.trim()
        }, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        // Toast が利用できない場合の代替手段
        if (this.$toast && typeof this.$toast.success === 'function') {
          this.$toast.success('申請を却下しました')
        } else {
          alert('申請を却下しました')
        }

        // データを再取得して更新
        try {
          await this.fetchRequests()
        } catch (fetchError) {
          console.error('データ再取得エラー:', fetchError)
          // エラーが発生しても却下は成功しているので、ページをリロード
          window.location.reload()
        }
      } catch (error) {
        console.error('却下エラー:', error)

        let errorMessage = '却下処理に失敗しました'
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.message) {
          errorMessage = error.message
        }

        // Toast が利用できない場合の代替手段
        if (this.$toast && typeof this.$toast.error === 'function') {
          this.$toast.error(errorMessage)
        } else {
          alert('エラー: ' + errorMessage)
        }
      } finally {
        this.processing = false
      }
    },

    filterRequests () {
      let filtered = [...this.requests]

      if (this.selectedStatus) {
        filtered = filtered.filter(request => request.status === this.selectedStatus)
      }

      this.filteredRequests = filtered
    },

    getStatusClass (status) {
      return {
        'admin-corrections__card--pending': status === 'pending',
        'admin-corrections__card--approved': status === 'approved',
        'admin-corrections__card--rejected': status === 'rejected'
      }
    },

    formatDate (dateString) {
      return new Date(dateString).toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'short'
      })
    },

    formatDateTime (dateString) {
      return new Date(dateString).toLocaleString('ja-JP')
    },

    formatTime (timeString) {
      if (!timeString) { return '未設定' }

      try {
        // ISO形式の日時文字列から時刻部分を抽出
        if (typeof timeString === 'string' && timeString.includes(':')) {
          // "2025-11-17 09:15:00" のような形式から時刻部分を抽出
          const timePart = timeString.split(' ')[1] || timeString
          const [hours, minutes] = timePart.split(':')
          return `${hours}:${minutes}`
        }

        // Dateオブジェクトの場合
        const date = new Date(timeString)
        if (isNaN(date.getTime())) {
          return '無効な時刻'
        }

        return date.toLocaleTimeString('ja-JP', {
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (error) {
        console.error('時刻フォーマットエラー:', error, timeString)
        return '無効な時刻'
      }
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-correction-requests.css" scoped></style>
