<template>
  <div class="attendance-table">
    <!-- テーブルヘッダー -->
    <div v-if="title" class="attendance-table__header">
      <h2 class="attendance-table__title">
        {{ title }}
      </h2>
      <p v-if="subtitle" class="attendance-table__subtitle">
        {{ subtitle }}
      </p>
    </div>

    <!-- フィルター（オプション） -->
    <div v-if="showFilters" class="attendance-table__filters">
      <slot name="filters" />
    </div>

    <!-- テーブル本体 -->
    <div class="attendance-table__wrapper">
      <table class="attendance-table__table">
        <thead>
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="column.headerClass"
            >
              {{ column.label }}
            </th>
            <th v-if="showActions">
              操作
            </th>
          </tr>
        </thead>
        <tbody>
          <!-- ローディング状態 -->
          <tr v-if="loading">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="attendance-table__cell--loading">
              <div class="attendance-table__loading">
                <div class="attendance-table__loading-spinner" />
                <span class="attendance-table__loading-text">読み込み中...</span>
              </div>
            </td>
          </tr>

          <!-- データなし状態 -->
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="attendance-table__cell--empty">
              <slot name="empty">
                <i class="fas fa-inbox attendance-table__empty-icon" />
                <p>データがありません</p>
              </slot>
            </td>
          </tr>

          <!-- データ行 -->
          <tr v-for="(item, index) in data" v-else :key="getItemKey(item, index)">
            <td
              v-for="column in columns"
              :key="column.key"
              :class="column.cellClass"
            >
              <slot :name="`cell-${column.key}`" :item="item" :value="getValueByKey(item, column.key)">
                <span v-if="column.type === 'date'">
                  {{ formatDate(getValueByKey(item, column.key)) }}
                </span>
                <span v-else-if="column.type === 'time'">
                  {{ getValueByKey(item, column.key) || '−' }}
                </span>
                <span v-else :class="getValueByKey(item, column.key) ? 'attendance-table__cell--text' : 'attendance-table__cell--dash'">
                  {{ getValueByKey(item, column.key) || '−' }}
                </span>
              </slot>
            </td>

            <!-- アクション列 -->
            <td v-if="showActions">
              <slot name="actions" :item="item" :index="index">
                <button
                  v-if="showDetailAction"
                  class="attendance-table__action attendance-table__action--detail"
                  @click="$emit('detail', item)"
                >
                  詳細
                </button>
                <button
                  v-if="showEditAction"
                  class="attendance-table__action attendance-table__action--edit"
                  @click="$emit('edit', item)"
                >
                  編集
                </button>
                <button
                  v-if="showDeleteAction"
                  class="attendance-table__action attendance-table__action--delete"
                  @click="$emit('delete', item)"
                >
                  削除
                </button>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ページネーション -->
    <div v-if="showPagination" class="attendance-table__pagination">
      <div class="attendance-table__pagination-text">
        {{ paginationText }}
      </div>
      <div class="attendance-table__pagination-controls">
        <button
          :disabled="currentPage === 1"
          class="attendance-table__pagination-button"
          @click="$emit('page-change', currentPage - 1)"
        >
          前へ
        </button>
        <span class="attendance-table__pagination-info">
          {{ currentPage }} / {{ totalPages }}
        </span>
        <button
          :disabled="currentPage === totalPages"
          class="attendance-table__pagination-button"
          @click="$emit('page-change', currentPage + 1)"
        >
          次へ
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AttendanceTable',

  props: {
    // 基本設定
    title: {
      type: String,
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
    },

    // データ
    data: {
      type: Array,
      required: true
    },
    columns: {
      type: Array,
      required: true
      // 例: [{ key: 'date', label: '日付', type: 'date' }]
    },
    loading: {
      type: Boolean,
      default: false
    },

    // キー設定
    itemKey: {
      type: String,
      default: 'id'
    },

    // 機能フラグ
    showFilters: {
      type: Boolean,
      default: false
    },
    showActions: {
      type: Boolean,
      default: true
    },
    showDetailAction: {
      type: Boolean,
      default: true
    },
    showEditAction: {
      type: Boolean,
      default: false
    },
    showDeleteAction: {
      type: Boolean,
      default: false
    },

    // ページネーション
    showPagination: {
      type: Boolean,
      default: true
    },
    currentPage: {
      type: Number,
      default: 1
    },
    totalPages: {
      type: Number,
      default: 1
    },
    totalItems: {
      type: Number,
      default: 0
    }
  },

  computed: {
    paginationText () {
      const start = (this.currentPage - 1) * this.data.length + 1
      const end = Math.min(start + this.data.length - 1, this.totalItems)
      return `${this.totalItems}件中 ${start}-${end}件を表示`
    }
  },

  methods: {
    getItemKey (item, index) {
      return item[this.itemKey] || index
    },

    getValueByKey (item, key) {
      // ネストされたキーにも対応（例: 'user.name'）
      return key.split('.').reduce((obj, k) => obj && obj[k], item)
    },

    formatDate (dateString) {
      if (!dateString) { return '−' }

      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        month: 'numeric',
        day: 'numeric'
      })
    }
  }
}
</script>

<style src="~/assets/css/components/AttendanceTable.css"></style>
