<template>
  <div class="staff-detail">
    <div class="staff-detail__page-header">
      <div class="staff-detail__header-content">
        <button
          type="button"
          class="staff-detail__back-btn"
          @click="$router.push('/admin/staff')"
        >
          <i class="fas fa-arrow-left" />
          スタッフ一覧に戻る
        </button>
        <h1 class="staff-detail__title">
          スタッフ詳細
        </h1>
      </div>
    </div>

    <div class="staff-detail__container">
      <div v-if="isLoading" class="staff-detail__loading">
        <LoadingSpinner />
        <p>スタッフ情報を読み込み中...</p>
      </div>

      <template v-else-if="staff">
        <!-- スタッフ基本情報 -->
        <div class="staff-detail__info-card">
          <div class="staff-detail__info-header">
            <div class="staff-detail__avatar-large">
              <i class="fas fa-user" />
            </div>
            <div class="staff-detail__info-main">
              <h2 class="staff-detail__name">
                {{ staff.name }}
              </h2>
              <p class="staff-detail__email">
                {{ staff.email }}
              </p>
              <div class="staff-detail__badges">
                <span
                  class="staff-detail__role-badge"
                  :class="staff.role === 'admin' ? 'staff-detail__role-badge--admin' : 'staff-detail__role-badge--user'"
                >
                  {{ staff.role === 'admin' ? '管理者' : '一般ユーザー' }}
                </span>
                <span
                  v-if="staff.email_verified_at"
                  class="staff-detail__verified-badge"
                >
                  <i class="fas fa-check-circle" />
                  メール認証済み
                </span>
                <span
                  v-else
                  class="staff-detail__unverified-badge"
                >
                  <i class="fas fa-exclamation-circle" />
                  メール未認証
                </span>
              </div>
            </div>
          </div>

          <div class="staff-detail__info-grid">
            <div class="staff-detail__info-item">
              <label>スタッフID</label>
              <p>{{ staff.id }}</p>
            </div>
            <div class="staff-detail__info-item">
              <label>登録日</label>
              <p>{{ formatDate(staff.created_at) }}</p>
            </div>
            <div class="staff-detail__info-item">
              <label>最終ログイン</label>
              <p>{{ staff.last_login_at ? formatDate(staff.last_login_at) : '未ログイン' }}</p>
            </div>
            <div class="staff-detail__info-item">
              <label>最終更新</label>
              <p>{{ formatDate(staff.updated_at) }}</p>
            </div>
          </div>
        </div>

        <!-- アクションボタン -->
        <div class="staff-detail__actions">
          <button
            type="button"
            class="staff-detail__btn staff-detail__btn--primary"
            @click="goToAttendance"
          >
            <i class="fas fa-calendar-alt" />
            {{ staff.name }}さんの勤怠
          </button>
          <button
            type="button"
            class="staff-detail__btn staff-detail__btn--secondary"
            @click="editStaff"
          >
            <i class="fas fa-edit" />
            編集
          </button>
        </div>

        <!-- 統計情報（将来的に追加） -->
        <div class="staff-detail__stats">
          <h3 class="staff-detail__section-title">
            勤怠統計（今月）
          </h3>
          <div class="staff-detail__stats-grid">
            <div class="staff-detail__stat-card">
              <div class="staff-detail__stat-icon staff-detail__stat-icon--days">
                <i class="fas fa-calendar-check" />
              </div>
              <div class="staff-detail__stat-info">
                <h4>出勤日数</h4>
                <p class="staff-detail__stat-value">
                  {{ monthlyStats.workDays }}日
                </p>
              </div>
            </div>
            <div class="staff-detail__stat-card">
              <div class="staff-detail__stat-icon staff-detail__stat-icon--hours">
                <i class="fas fa-clock" />
              </div>
              <div class="staff-detail__stat-info">
                <h4>総勤務時間</h4>
                <p class="staff-detail__stat-value">
                  {{ monthlyStats.totalHours }}時間
                </p>
              </div>
            </div>
            <div class="staff-detail__stat-card">
              <div class="staff-detail__stat-icon staff-detail__stat-icon--late">
                <i class="fas fa-exclamation-triangle" />
              </div>
              <div class="staff-detail__stat-info">
                <h4>遅刻回数</h4>
                <p class="staff-detail__stat-value">
                  {{ monthlyStats.lateCount }}回
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="staff-detail__error">
        <i class="fas fa-exclamation-circle" />
        <p>スタッフ情報が見つかりませんでした</p>
        <button
          type="button"
          class="staff-detail__btn staff-detail__btn--secondary"
          @click="$router.push('/admin/staff')"
        >
          スタッフ一覧に戻る
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StaffDetail',
  middleware: ['auth', 'verified', 'admin'],

  data () {
    return {
      staff: null,
      isLoading: false,
      monthlyStats: {
        workDays: 0,
        totalHours: 0,
        lateCount: 0
      }
    }
  },

  async mounted () {
    await this.loadStaffData()
  },

  methods: {
    async loadStaffData () {
      try {
        this.isLoading = true
        const staffId = this.$route.params.id

        const response = await this.$axios.get(`/api/staff/${staffId}`, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.staff = response.data
        await this.loadMonthlyStats()
      } catch (error) {
        console.error('スタッフ情報取得エラー:', error)

        // 開発中: ダミーデータ
        this.staff = {
          id: this.$route.params.id,
          name: '山田太郎',
          email: 'yamada@example.com',
          role: 'user',
          email_verified_at: '2024-01-15T10:00:00Z',
          created_at: '2024-01-01T09:00:00Z',
          updated_at: '2024-11-20T14:30:00Z',
          last_login_at: '2024-11-27T09:15:00Z'
        }
        this.monthlyStats = {
          workDays: 18,
          totalHours: 144,
          lateCount: 2
        }

        if (this.$toast) {
          this.$toast.warning('デモデータを表示しています（API未実装）')
        }
      } finally {
        this.isLoading = false
      }
    },

    async loadMonthlyStats () {
      try {
        const response = await this.$axios.get(`/api/staff/${this.staff.id}/stats/monthly`, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.monthlyStats = response.data
      } catch (error) {
        console.error('月次統計取得エラー:', error)
        // ダミーデータは mounted で設定済み
      }
    },

    formatDate (dateString) {
      if (!dateString) { return '未設定' }
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    goToAttendance () {
      this.$router.push(`/admin/staff/${this.staff.id}/attendance`)
    },

    editStaff () {
      if (this.$toast) {
        this.$toast.info('編集機能は開発中です')
      } else {
        alert('編集機能は開発中です')
      }
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-staff-detail.css" scoped></style>
