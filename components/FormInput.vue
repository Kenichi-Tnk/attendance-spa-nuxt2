<template>
  <div class="form-group">
    <!-- ラベル -->
    <label
      v-if="label"
      :for="inputId"
      :class="labelClasses"
      class="block text-sm font-medium mb-2"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <!-- 入力フィールド -->
    <div class="relative">
      <!-- プレフィックスアイコン -->
      <div v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i :class="prefixIcon" class="text-gray-400"></i>
      </div>
      
      <!-- テキストエリア -->
      <textarea
        v-if="type === 'textarea'"
        :id="inputId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        :class="inputClasses"
        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent transition-colors resize-vertical"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      ></textarea>
      
      <!-- セレクト -->
      <select
        v-else-if="type === 'select'"
        :id="inputId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="inputClasses"
        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
        @change="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      >
        <option v-for="option in options" :key="getOptionValue(option)" :value="getOptionValue(option)">
          {{ getOptionLabel(option) }}
        </option>
      </select>
      
      <!-- 通常の入力フィールド -->
      <input
        v-else
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :class="inputClasses"
        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      >
      
      <!-- サフィックスアイコン -->
      <div v-if="suffixIcon" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <i :class="suffixIcon" class="text-gray-400"></i>
      </div>
      
      <!-- クリアボタン -->
      <button
        v-if="clearable && modelValue"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
        @click="clearValue"
      >
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>
    
    <!-- ヘルプテキスト -->
    <p v-if="helpText" class="mt-2 text-sm text-gray-500">
      {{ helpText }}
    </p>
    
    <!-- エラーメッセージ -->
    <p v-if="error" class="mt-2 text-sm text-red-600">
      <i class="fas fa-exclamation-circle mr-1"></i>
      {{ error }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'FormInput',
  
  props: {
    // v-model
    modelValue: {
      type: [String, Number],
      default: ''
    },
    
    // 基本設定
    type: {
      type: String,
      default: 'text',
      validator: value => [
        'text', 'email', 'password', 'number', 'tel', 'url', 'search',
        'date', 'datetime-local', 'month', 'week', 'time',
        'textarea', 'select'
      ].includes(value)
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    helpText: {
      type: String,
      default: ''
    },
    
    // バリデーション
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    
    // 状態
    disabled: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    
    // 数値・日付用
    min: {
      type: [String, Number],
      default: undefined
    },
    max: {
      type: [String, Number],
      default: undefined
    },
    step: {
      type: [String, Number],
      default: undefined
    },
    
    // テキストエリア用
    rows: {
      type: Number,
      default: 3
    },
    
    // セレクト用
    options: {
      type: Array,
      default: () => []
    },
    optionValue: {
      type: String,
      default: 'value'
    },
    optionLabel: {
      type: String,
      default: 'label'
    },
    
    // アイコン
    prefixIcon: {
      type: String,
      default: ''
    },
    suffixIcon: {
      type: String,
      default: ''
    },
    clearable: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['update:modelValue', 'focus', 'blur', 'clear'],
  
  data() {
    return {
      focused: false
    }
  },
  
  computed: {
    inputId() {
      return `input-${this._uid || Math.random().toString(36).substr(2, 9)}`
    },
    
    labelClasses() {
      const classes = ['text-gray-700']
      if (this.disabled) classes.push('text-gray-400')
      return classes.join(' ')
    },
    
    inputClasses() {
      const classes = []
      
      // 基本スタイル
      if (this.prefixIcon) classes.push('pl-10')
      if (this.suffixIcon || this.clearable) classes.push('pr-10')
      
      // 状態に応じたスタイル
      if (this.error) {
        classes.push('border-red-300 focus:ring-red-500 focus:border-red-500')
      } else if (this.focused) {
        classes.push('border-blue-300 focus:ring-blue-500 focus:border-blue-500')
      } else {
        classes.push('border-gray-300 focus:ring-blue-500 focus:border-blue-500')
      }
      
      if (this.disabled) {
        classes.push('bg-gray-50 text-gray-400 cursor-not-allowed')
      } else if (this.readonly) {
        classes.push('bg-gray-50 text-gray-700')
      }
      
      if (this.loading) {
        classes.push('animate-pulse')
      }
      
      return classes.join(' ')
    }
  },
  
  methods: {
    handleInput(event) {
      this.$emit('update:modelValue', event.target.value)
    },
    
    handleFocus(event) {
      this.focused = true
      this.$emit('focus', event)
    },
    
    handleBlur(event) {
      this.focused = false
      this.$emit('blur', event)
    },
    
    clearValue() {
      this.$emit('update:modelValue', '')
      this.$emit('clear')
    },
    
    getOptionValue(option) {
      return typeof option === 'object' ? option[this.optionValue] : option
    },
    
    getOptionLabel(option) {
      return typeof option === 'object' ? option[this.optionLabel] : option
    }
  }
}
</script>
