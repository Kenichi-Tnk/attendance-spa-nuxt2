&lt;template&gt;
  &lt;div
    :class="cardClasses"
    class="bg-white rounded-lg shadow-md p-6 transition-all duration-200"
  &gt;
    &lt;div class="flex items-center"&gt;
      &lt;!-- アイコン --&gt;
      &lt;div v-if="icon" :class="iconClasses" class="flex-shrink-0 p-3 rounded-lg mr-4"&gt;
        &lt;i :class="icon" class="text-2xl"&gt;&lt;/i&gt;
      &lt;/div&gt;
      
      &lt;!-- コンテンツ --&gt;
      &lt;div class="flex-1 min-w-0"&gt;
        &lt;!-- 値 --&gt;
        &lt;div class="flex items-baseline"&gt;
          &lt;div :class="valueClasses" class="text-2xl font-bold truncate"&gt;
            {{ displayValue }}
          &lt;/div&gt;
          &lt;div v-if="unit" class="ml-2 text-sm text-gray-500"&gt;
            {{ unit }}
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;!-- ラベル --&gt;
        &lt;div class="text-sm text-gray-600 mt-1 truncate"&gt;
          {{ label }}
        &lt;/div&gt;
        
        &lt;!-- 変化量（オプション） --&gt;
        &lt;div v-if="change !== null" class="flex items-center mt-2"&gt;
          &lt;i :class="changeIcon" class="text-xs mr-1"&gt;&lt;/i&gt;
          &lt;span :class="changeClasses" class="text-xs font-medium"&gt;
            {{ changeText }}
          &lt;/span&gt;
          &lt;span v-if="changePeriod" class="text-xs text-gray-400 ml-1"&gt;
            {{ changePeriod }}
          &lt;/span&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;!-- 追加コンテンツ --&gt;
      &lt;div v-if="$slots.extra" class="flex-shrink-0 ml-4"&gt;
        &lt;slot name="extra"&gt;&lt;/slot&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;!-- ボトムコンテンツ --&gt;
    &lt;div v-if="$slots.bottom" class="mt-4 pt-4 border-t border-gray-100"&gt;
      &lt;slot name="bottom"&gt;&lt;/slot&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
  name: 'StatCard',
  
  props: {
    // 基本データ
    value: {
      type: [String, Number],
      required: true
    },
    label: {
      type: String,
      required: true
    },
    unit: {
      type: String,
      default: ''
    },
    
    // 外観
    icon: {
      type: String,
      default: ''
    },
    color: {
      type: String,
      default: 'blue',
      validator: value =&gt; ['blue', 'green', 'yellow', 'red', 'purple', 'gray', 'indigo'].includes(value)
    },
    borderPosition: {
      type: String,
      default: 'left',
      validator: value =&gt; ['left', 'top', 'none'].includes(value)
    },
    
    // 変化量
    change: {
      type: Number,
      default: null
    },
    changePeriod: {
      type: String,
      default: ''
    },
    
    // インタラクション
    clickable: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  
  computed: {
    displayValue() {
      if (this.loading) return '---'
      
      if (typeof this.value === 'number') {
        return this.value.toLocaleString()
      }
      return this.value
    },
    
    cardClasses() {
      const classes = []
      
      // ボーダー
      if (this.borderPosition === 'left') {
        classes.push(`border-l-4 border-${this.color}-500`)
      } else if (this.borderPosition === 'top') {
        classes.push(`border-t-4 border-${this.color}-500`)
      }
      
      // クリック可能
      if (this.clickable) {
        classes.push('hover:shadow-lg cursor-pointer')
      }
      
      // ローディング
      if (this.loading) {
        classes.push('animate-pulse')
      }
      
      return classes.join(' ')
    },
    
    iconClasses() {
      return `bg-${this.color}-100 text-${this.color}-600`
    },
    
    valueClasses() {
      return `text-${this.color === 'gray' ? 'gray-800' : 'gray-900'}`
    },
    
    changeIcon() {
      if (this.change === null) return ''
      return this.change &gt; 0 ? 'fas fa-arrow-up' : this.change &lt; 0 ? 'fas fa-arrow-down' : 'fas fa-minus'
    },
    
    changeClasses() {
      if (this.change === null) return ''
      return this.change &gt; 0 ? 'text-green-600' : this.change &lt; 0 ? 'text-red-600' : 'text-gray-600'
    },
    
    changeText() {
      if (this.change === null) return ''
      const prefix = this.change &gt; 0 ? '+' : ''
      return `${prefix}${this.change}%`
    }
  },
  
  methods: {
    handleClick() {
      if (this.clickable) {
        this.$emit('click')
      }
    }
  }
}
&lt;/script&gt;