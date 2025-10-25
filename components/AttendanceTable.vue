<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- テーブルヘッダー -->
    <div v-if="title" class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-800">{{ title }}</h2>
      <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
    </div>
    
    <!-- フィルター（オプション） -->
    <div v-if="showFilters" class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <slot name="filters"></slot>
    </div>
    
    <!-- テーブル本体 -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              :class="column.headerClass"
            >
              {{ column.label }}
            </th>
            <th v-if="showActions" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              操作
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- ローディング状態 -->
          <tr v-if="loading">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="px-6 py-12 text-center">
              <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-500">読み込み中...</span>
              </div>
            </td>
          </tr>
          
          <!-- データなし状態 -->
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="px-6 py-12 text-center text-gray-500">
              <slot name="empty">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                <p>データがありません</p>
              </slot>
            </td>
          </tr>
          
          <!-- データ行 -->
          <tr v-else v-for="(item, index) in data" :key="getItemKey(item, index)" class="hover:bg-gray-50">
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm"
              :class="column.cellClass"
            >
              <slot :name="`cell-${column.key}`" :item="item" :value="getValueByKey(item, column.key)">
                <span v-if="column.type === 'date'">
                  {{ formatDate(getValueByKey(item, column.key)) }}
                </span>
                <span v-else-if="column.type === 'time'">
                  {{ getValueByKey(item, column.key) || '−' }}
                </span>
                <span v-else-if="column.type === 'status'">
                  <StatusBadge :status="getValueByKey(item, column.key)" />
                </span>
                <span v-else class="text-gray-900">
                  {{ getValueByKey(item, column.key) || '−' }}
                </span>
              </slot>
            </td>
            
            <!-- アクション列 -->
            <td v-if="showActions" class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <slot name="actions" :item="item" :index="index">
                <button
                  v-if="showDetailAction"
                  @click="$emit('detail', item)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  詳細
                </button>
                <button
                  v-if="showEditAction"
                  @click="$emit('edit', item)"
                  class="text-yellow-600 hover:text-yellow-900 mr-3"
                >
                  編集
                </button>
                <button
                  v-if="showDeleteAction"
                  @click="$emit('delete', item)"
                  class="text-red-600 hover:text-red-900"
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
    <div v-if="showPagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        {{ paginationText }}
      </div>
      <div class="flex space-x-2">
        <button
          :disabled="currentPage === 1"
          @click="$emit('page-change', currentPage - 1)"
          class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
        >
          前へ
        </button>
        <span class="px-3 py-1 text-sm text-gray-700">
          {{ currentPage }} / {{ totalPages }}
        </span>
        <button
          :disabled="currentPage === totalPages"
          @click="$emit('page-change', currentPage + 1)"
          class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
        >
          次へ
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import StatusBadge from './StatusBadge.vue'

export default {
  name: 'AttendanceTable',
  components: {
    StatusBadge
  },
  
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
    paginationText() {
      const start = (this.currentPage - 1) * this.data.length + 1
      const end = Math.min(start + this.data.length - 1, this.totalItems)
      return `${this.totalItems}件中 ${start}-${end}件を表示`
    }
  },
  
  methods: {
    getItemKey(item, index) {
      return item[this.itemKey] || index
    },
    
    getValueByKey(item, key) {
      // ネストされたキーにも対応（例: 'user.name'）
      return key.split('.').reduce((obj, k) => obj && obj[k], item)
    },
    
    formatDate(dateString) {
      if (!dateString) return '−'
      
      const date = new Date(dateString)
      return date.toLocaleDateString('ja-JP', {
        month: 'numeric',
        day: 'numeric'
      })
    }
  }
}
</script>