&lt;template&gt;
  &lt;div class="mb-8"&gt;
    &lt;!-- パンくずリスト --&gt;
    &lt;nav v-if="breadcrumbs &amp;&amp; breadcrumbs.length &gt; 0" class="flex items-center space-x-2 text-sm text-gray-500 mb-4"&gt;
      &lt;template v-for="(breadcrumb, index) in breadcrumbs" :key="index"&gt;
        &lt;nuxt-link
          v-if="breadcrumb.to &amp;&amp; index &lt; breadcrumbs.length - 1"
          :to="breadcrumb.to"
          class="hover:text-blue-600 transition-colors"
        &gt;
          {{ breadcrumb.text }}
        &lt;/nuxt-link&gt;
        &lt;span v-else :class="{ 'text-gray-900 font-medium': index === breadcrumbs.length - 1 }"&gt;
          {{ breadcrumb.text }}
        &lt;/span&gt;
        &lt;span v-if="index &lt; breadcrumbs.length - 1" class="text-gray-300"&gt;/&lt;/span&gt;
      &lt;/template&gt;
    &lt;/nav&gt;
    
    &lt;!-- ヘッダーコンテンツ --&gt;
    &lt;div class="flex items-start justify-between"&gt;
      &lt;div class="flex-1"&gt;
        &lt;!-- アイコン付きタイトル --&gt;
        &lt;div class="flex items-center mb-2"&gt;
          &lt;i v-if="icon" :class="icon" class="text-2xl mr-3" :style="{ color: iconColor }"&gt;&lt;/i&gt;
          &lt;h1 class="text-3xl font-bold text-gray-900"&gt;{{ title }}&lt;/h1&gt;
        &lt;/div&gt;
        
        &lt;!-- サブタイトル --&gt;
        &lt;p v-if="subtitle" class="text-gray-600 mt-2"&gt;{{ subtitle }}&lt;/p&gt;
        
        &lt;!-- 追加情報スロット --&gt;
        &lt;div v-if="$slots.info" class="mt-3"&gt;
          &lt;slot name="info"&gt;&lt;/slot&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;!-- アクションボタン --&gt;
      &lt;div v-if="$slots.actions" class="flex-shrink-0 ml-6"&gt;
        &lt;slot name="actions"&gt;&lt;/slot&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;!-- 区切り線 --&gt;
    &lt;div v-if="showDivider" class="border-b border-gray-200 mt-6"&gt;&lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
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
      default: () =&gt; []
      // 例: [{ text: 'ホーム', to: '/' }, { text: '勤怠一覧', to: '/attendance' }, { text: '詳細' }]
    },
    showDivider: {
      type: Boolean,
      default: true
    }
  }
}
&lt;/script&gt;