<template>
  <div class="mb-8">
    <!-- パンくずリスト -->
    <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
      <template v-for="(breadcrumb, index) in breadcrumbs" :key="index">
        <nuxt-link
          v-if="breadcrumb.to && index < breadcrumbs.length - 1"
          :to="breadcrumb.to"
          class="hover:text-blue-600 transition-colors"
        >
          {{ breadcrumb.text }}
        </nuxt-link>
        <span v-else :class="{ 'text-gray-900 font-medium': index === breadcrumbs.length - 1 }">
          {{ breadcrumb.text }}
        </span>
        <span v-if="index < breadcrumbs.length - 1" class="text-gray-300">/</span>
      </template>
    </nav>
    
    <!-- ヘッダーコンテンツ -->
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <!-- アイコン付きタイトル -->
        <div class="flex items-center mb-2">
          <i v-if="icon" :class="icon" class="text-2xl mr-3" :style="{ color: iconColor }"></i>
          <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
        </div>
        
        <!-- サブタイトル -->
        <p v-if="subtitle" class="text-gray-600 mt-2">{{ subtitle }}</p>
        
        <!-- 追加情報スロット -->
        <div v-if="$slots.info" class="mt-3">
          <slot name="info"></slot>
        </div>
      </div>
      
      <!-- アクションボタン -->
      <div v-if="$slots.actions" class="flex-shrink-0 ml-6">
        <slot name="actions"></slot>
      </div>
    </div>
    
    <!-- 区切り線 -->
    <div v-if="showDivider" class="border-b border-gray-200 mt-6"></div>
  </div>
</template>

<script>
export default {
  name: 'PageHeader',
  
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      default: ''
    },
    icon: {
      type: String,
      default: ''
    },
    iconColor: {
      type: String,
      default: '#3B82F6' // blue-500
    },
    breadcrumbs: {
      type: Array,
      default: () => []
      // 例: [{ text: 'ホーム', to: '/' }, { text: '勤怠一覧', to: '/attendance' }, { text: '詳細' }]
    },
    showDivider: {
      type: Boolean,
      default: true
    }
  }
}
</script>
