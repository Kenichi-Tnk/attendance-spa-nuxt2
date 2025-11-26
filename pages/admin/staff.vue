<template>
  <div class="admin-staff">
    <PageHeader
      title="スタッフ管理"
      subtitle="登録されているスタッフ情報を管理"
      icon="fas fa-users"
      icon-color="#3B82F6"
    />

    <div class="admin-staff__container">
      <!-- スタッフ統計 -->
      <div class="admin-staff__stats">
        <div class="admin-staff__stat-card">
          <div class="admin-staff__stat-icon admin-staff__stat-icon--total">
            <i class="fas fa-users"></i>
          </div>
          <div class="admin-staff__stat-info">
            <h3>{{ totalStaff }}</h3>
            <p>総スタッフ数</p>
          </div>
        </div>

        <div class="admin-staff__stat-card">
          <div class="admin-staff__stat-icon admin-staff__stat-icon--verified">
            <i class="fas fa-user-check"></i>
          </div>
          <div class="admin-staff__stat-info">
            <h3>{{ verifiedStaff }}</h3>
            <p>メール認証済み</p>
          </div>
        </div>

        <div class="admin-staff__stat-card">
          <div class="admin-staff__stat-icon admin-staff__stat-icon--unverified">
            <i class="fas fa-user-clock"></i>
          </div>
          <div class="admin-staff__stat-info">
            <h3>{{ unverifiedStaff }}</h3>
            <p>メール未認証</p>
          </div>
        </div>

        <div class="admin-staff__stat-card">
          <div class="admin-staff__stat-icon admin-staff__stat-icon--admin">
            <i class="fas fa-user-shield"></i>
          </div>
          <div class="admin-staff__stat-info">
            <h3>{{ adminStaff }}</h3>
            <p>管理者</p>
          </div>
        </div>
      </div>

      <!-- スタッフ一覧 -->
      <div class="admin-staff__content">
        <div class="admin-staff__header">
          <h2>スタッフ一覧</h2>
          <div class="admin-staff__actions">
            <button 
              @click="showCreateModal = true"
              class="admin-staff__btn admin-staff__btn--primary"
            >
              <i class="fas fa-user-plus"></i>
              スタッフを招待
            </button>
          </div>
        </div>

        <!-- 検索・フィルター -->
        <div class="admin-staff__filters">
          <div class="admin-staff__search">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="名前またはメールアドレスで検索..."
              class="admin-staff__search-input"
              @input="handleSearch"
            />
            <i class="fas fa-search admin-staff__search-icon"></i>
          </div>
          
          <div class="admin-staff__filter-group">
            <select v-model="roleFilter" @change="handleFilter" class="admin-staff__select">
              <option value="">すべての役割</option>
              <option value="admin">管理者</option>
              <option value="user">一般ユーザー</option>
            </select>
            
            <select v-model="verifiedFilter" @change="handleFilter" class="admin-staff__select">
              <option value="">すべての状態</option>
              <option value="true">認証済み</option>
              <option value="false">未認証</option>
            </select>
            
            <button 
              @click="clearFilters" 
              class="admin-staff__btn admin-staff__btn--secondary"
              v-if="searchQuery || roleFilter || verifiedFilter"
            >
              <i class="fas fa-times"></i>
              フィルターをクリア
            </button>
          </div>
        </div>

        <div v-if="isLoading" class="admin-staff__loading">
          <LoadingSpinner />
          <p>スタッフ情報を読み込み中...</p>
        </div>

        <div v-else class="admin-staff__table-container">
          <table class="admin-staff__table">
            <thead>
              <tr>
                <th>スタッフ</th>
                <th>メールアドレス</th>
                <th>役割</th>
                <th>登録日</th>
                <th>最終ログイン</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="staff in staffList" :key="staff.id" class="admin-staff__row">
                <td class="admin-staff__staff-info">
                  <div class="admin-staff__avatar">
                    <i class="fas fa-user"></i>
                  </div>
                  <div>
                    <div class="admin-staff__name">{{ staff.name }}</div>
                    <div class="admin-staff__id">ID: {{ staff.id }}</div>
                  </div>
                </td>
                <td>{{ staff.email }}</td>
                <td>
                  <span
                    class="admin-staff__role-badge"
                    :class="getRoleClass(staff.role)"
                  >
                    {{ staff.role === 'admin' ? '管理者' : '一般' }}
                  </span>
                </td>
                <td>{{ formatDate(staff.created_at) }}</td>
                <td>{{ staff.last_login_at ? formatDate(staff.last_login_at) : '未ログイン' }}</td>
                <td class="admin-staff__actions-cell">
                  <button 
                    class="admin-staff__action-btn admin-staff__action-btn--edit"
                    @click="editStaff(staff)"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    class="admin-staff__action-btn admin-staff__action-btn--delete"
                    @click="deleteStaff(staff)"
                    :disabled="staff.role === 'admin' && staff.id === currentUser.id"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- ページネーション情報 -->
          <div class="admin-staff__pagination-info">
            <span class="admin-staff__record-count">
              表示中: {{ staffList.length }}件 / 全{{ paginationInfo.total }}件
            </span>
            <span v-if="paginationInfo.last_page > 1" class="admin-staff__page-info">
              ページ {{ paginationInfo.current_page }} / {{ paginationInfo.last_page }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 新規スタッフ作成モーダル -->
    <div v-if="showCreateModal" class="admin-staff__modal-overlay" @click="closeCreateModal">
      <div class="admin-staff__modal" @click.stop>
        <div class="admin-staff__modal-header">
          <h3>スタッフ招待</h3>
          <button @click="closeCreateModal" class="admin-staff__modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="admin-staff__modal-description">
          <p>新しいスタッフを招待します。招待メールが送信され、スタッフは初回ログイン時にパスワードを設定します。</p>
        </div>
        
        <form @submit.prevent="createStaff" class="admin-staff__form">
          <div class="admin-staff__form-group">
            <label for="name" class="admin-staff__label">名前 *</label>
            <input
              id="name"
              v-model="newStaff.name"
              type="text"
              required
              class="admin-staff__input"
              placeholder="スタッフの名前を入力"
            />
          </div>
          
          <div class="admin-staff__form-group">
            <label for="email" class="admin-staff__label">メールアドレス *</label>
            <input
              id="email"
              v-model="newStaff.email"
              type="email"
              required
              class="admin-staff__input"
              placeholder="example@company.com"
            />
            <small class="admin-staff__help-text">
              このメールアドレスに招待メールが送信されます
            </small>
          </div>
          
          <div class="admin-staff__form-group">
            <label for="role" class="admin-staff__label">役割 *</label>
            <select
              id="role"
              v-model="newStaff.role"
              required
              class="admin-staff__input"
            >
              <option value="">選択してください</option>
              <option value="user">一般ユーザー</option>
              <option value="admin">管理者</option>
            </select>
          </div>
          
          <div class="admin-staff__form-actions">
            <button
              type="button"
              @click="closeCreateModal"
              class="admin-staff__btn admin-staff__btn--secondary"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isCreating"
              class="admin-staff__btn admin-staff__btn--primary"
            >
              <i v-if="isCreating" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-paper-plane"></i>
              {{ isCreating ? '招待中...' : '招待を送信' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminStaff',
  middleware: ['auth', 'verified', 'admin'],

  data() {
    return {
      staffList: [],
      paginationInfo: {
        current_page: 1,
        last_page: 1,
        total: 0
      },
      statistics: {
        total: 0,
        verified: 0,
        unverified: 0,
        admin: 0,
        user: 0
      },
      isLoading: false,
      searchQuery: '',
      roleFilter: '',
      verifiedFilter: '',
      searchTimeout: null,
      showCreateModal: false,
      isCreating: false,
      newStaff: {
        name: '',
        email: '',
        role: ''
      }
    }
  },

  computed: {
    currentUser() {
      return this.$store.getters['auth/user']
    },

    totalStaff() {
      return this.statistics.total
    },

    verifiedStaff() {
      return this.statistics.verified
    },

    unverifiedStaff() {
      return this.statistics.unverified
    },

    adminStaff() {
      return this.statistics.admin
    },

    userStaff() {
      return this.statistics.user
    }
  },

  async mounted() {
    await Promise.all([
      this.fetchStaffList(),
      this.fetchStatistics()
    ])
  },

  methods: {
    async fetchStaffList() {
      try {
        this.isLoading = true
        
        // フィルターパラメータを構築
        const params = new URLSearchParams()
        if (this.searchQuery) params.append('search', this.searchQuery)
        if (this.roleFilter) params.append('role', this.roleFilter)
        if (this.verifiedFilter) params.append('verified', this.verifiedFilter)
        
        const queryString = params.toString()
        const url = queryString ? `/api/staff?${queryString}` : '/api/staff'
        
        const response = await this.$axios.get(url, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        // ページネーション対応
        if (response.data.data) {
          this.staffList = response.data.data
          this.paginationInfo = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total
          }
        } else {
          this.staffList = response.data
        }
      } catch (error) {
        console.error('スタッフ一覧取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('スタッフ情報の取得に失敗しました')
        } else {
          alert('スタッフ情報の取得に失敗しました')
        }
      } finally {
        this.isLoading = false
      }
    },

    async fetchStatistics() {
      try {
        const response = await this.$axios.get('/api/staff/statistics', {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        this.statistics = response.data
      } catch (error) {
        console.error('統計情報取得エラー:', error)
        if (this.$toast) {
          this.$toast.error('統計情報の取得に失敗しました')
        } else {
          alert('統計情報の取得に失敗しました')
        }
      }
    },

    handleSearch() {
      // デバウンス処理：入力から500ms後に検索実行
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.fetchStaffList()
      }, 500)
    },

    handleFilter() {
      // フィルター変更時は即座に実行
      this.fetchStaffList()
    },

    clearFilters() {
      this.searchQuery = ''
      this.roleFilter = ''
      this.verifiedFilter = ''
      this.fetchStaffList()
    },

    getRoleClass(role) {
      return {
        'admin-staff__role-badge--admin': role === 'admin',
        'admin-staff__role-badge--user': role === 'user'
      }
    },

    formatDate(dateString) {
      if (!dateString) return '未設定'
      return new Date(dateString).toLocaleDateString('ja-JP')
    },

    closeCreateModal() {
      this.showCreateModal = false
      this.newStaff = {
        name: '',
        email: '',
        role: ''
      }
    },

    async createStaff() {
      try {
        this.isCreating = true
        
        await this.$axios.post('/api/staff', this.newStaff, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        if (this.$toast) {
          this.$toast.success('スタッフ招待を送信しました')
        } else {
          alert('スタッフ招待を送信しました')
        }
        
        this.closeCreateModal()
        await Promise.all([
          this.fetchStaffList(),
          this.fetchStatistics()
        ])
      } catch (error) {
        console.error('スタッフ作成エラー:', error)
        const errorMessage = error.response?.data?.message || 'スタッフ招待の送信に失敗しました'
        
        if (this.$toast) {
          this.$toast.error(errorMessage)
        } else {
          alert(errorMessage)
        }
      } finally {
        this.isCreating = false
      }
    },

    editStaff(staff) {
      // 編集機能の実装
      if (this.$toast) {
        this.$toast.info('編集機能は開発中です')
      } else {
        alert('編集機能は開発中です')
      }
    },

    async deleteStaff(staff) {
      if (staff.role === 'admin' && staff.id === this.currentUser.id) {
        this.$toast.error('自分自身は削除できません')
        return
      }

      if (!confirm(`${staff.name}さんを削除しますか？この操作は取り消せません。`)) {
        return
      }

      try {
        await this.$axios.delete(`/api/staff/${staff.id}`, {
          headers: {
            Authorization: `Bearer ${this.$store.getters['auth/token']}`
          }
        })

        if (this.$toast) {
          this.$toast.success('スタッフを削除しました')
        } else {
          alert('スタッフを削除しました')
        }
        await Promise.all([
          this.fetchStaffList(),
          this.fetchStatistics()
        ])
      } catch (error) {
        console.error('削除エラー:', error)
        if (this.$toast) {
          this.$toast.error('スタッフの削除に失敗しました')
        } else {
          alert('スタッフの削除に失敗しました')
        }
      }
    }
  }
}
</script>

<style src="@/assets/css/pages/admin-staff.css" scoped></style>