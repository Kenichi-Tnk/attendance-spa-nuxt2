<template>
  <div class="page-header">
    <!-- パンくずリスト -->
    <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="page-header__breadcrumbs">
      <template v-for="(breadcrumb, index) in breadcrumbs">
        <nuxt-link
          v-if="breadcrumb.to && index < breadcrumbs.length - 1"
          :key="`link-${index}`"
          :to="breadcrumb.to"
          class="page-header__breadcrumb-link"
        >
          {{ breadcrumb.text }}
        </nuxt-link>
        <span
          v-else
          :key="`span-${index}`"
          :class="{ 'page-header__breadcrumb-current': index === breadcrumbs.length - 1 }"
        >
          {{ breadcrumb.text }}
        </span>
        <span
          v-if="index < breadcrumbs.length - 1"
          :key="`separator-${index}`"
          class="page-header__breadcrumb-separator"
        >
          /
        </span>
      </template>
    </nav>

    <!-- ヘッダーコンテンツ -->
    <div class="page-header__content">
      <div class="page-header__main">
        <!-- アイコン付きタイトル -->
        <div class="page-header__title-container">
          <i v-if="icon" :class="icon" class="page-header__icon" :style="{ color: iconColor }"></i>
          <h1 class="page-header__title">{{ title }}</h1>
        </div>

        <!-- サブタイトル -->
        <p v-if="subtitle" class="page-header__subtitle">{{ subtitle }}</p>

        <!-- 追加情報スロット -->
        <div v-if="$slots.info" class="page-header__info">
          <slot name="info"></slot>
        </div>
      </div>

      <!-- アクションボタン -->
      <div v-if="$slots.actions" class="page-header__actions">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- 区切り線 -->
    <div v-if="showDivider" class="page-header__divider"></div>
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

<style src="~/assets/css/components/PageHeader.css"></style>
