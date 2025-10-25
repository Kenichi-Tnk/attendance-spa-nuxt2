<template>
  <div
    :class="cardClasses"
    class="bg-white rounded-lg shadow-md p-6 transition-all duration-200"
    @click="handleClick"
  >
    <div class="flex items-center">
      <!-- アイコン -->
      <div v-if="icon" :class="iconClasses" class="flex-shrink-0 p-3 rounded-lg mr-4">
        <i :class="icon" class="text-2xl"></i>
      </div>
      
      <!-- コンテンツ -->
      <div class="flex-1 min-w-0">
        <!-- 値 -->
        <div class="flex items-baseline">
          <div :class="valueClasses" class="text-2xl font-bold truncate">
            {{ displayValue }}
          </div>
          <div v-if="unit" class="ml-2 text-sm text-gray-500">
            {{ unit }}
          </div>
        </div>
        
        <!-- ラベル -->
        <div class="text-sm text-gray-600 mt-1 truncate">
          {{ label }}
        </div>
        
        <!-- 変化量（オプション） -->
        <div v-if="change !== null" class="flex items-center mt-2">
          <i :class="changeIcon" class="text-xs mr-1"></i>
          <span :class="changeClasses" class="text-xs font-medium">
            {{ changeText }}
          </span>
          <span v-if="changePeriod" class="text-xs text-gray-400 ml-1">
            {{ changePeriod }}
          </span>
        </div>
      </div>
      
      <!-- 追加コンテンツ -->
      <div v-if="$slots.extra" class="flex-shrink-0 ml-4">
        <slot name="extra"></slot>
      </div>
    </div>
    
    <!-- ボトムコンテンツ -->
    <div v-if="$slots.bottom" class="mt-4 pt-4 border-t border-gray-100">
      <slot name="bottom"></slot>
    </div>
  </div>
</template>

<script>
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
      validator: value => ['blue', 'green', 'yellow', 'red', 'purple', 'gray', 'indigo'].includes(value)
    },
    borderPosition: {
      type: String,
      default: 'left',
      validator: value => ['left', 'top', 'none'].includes(value)
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
      return this.change > 0 ? 'fas fa-arrow-up' : this.change < 0 ? 'fas fa-arrow-down' : 'fas fa-minus'
    },
    
    changeClasses() {
      if (this.change === null) return ''
      return this.change > 0 ? 'text-green-600' : this.change < 0 ? 'text-red-600' : 'text-gray-600'
    },
    
    changeText() {
      if (this.change === null) return ''
      const prefix = this.change > 0 ? '+' : ''
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
</script>
