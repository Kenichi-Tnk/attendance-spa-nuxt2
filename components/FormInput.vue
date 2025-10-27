<template>
  <div class="form-input">
    <!-- ラベル -->
    <label
      v-if="label"
      :for="inputId"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="form-input__required">*</span>
    </label>

    <!-- 入力フィールド -->
    <div class="form-input__container">
      <!-- プレフィックスアイコン -->
      <div v-if="prefixIcon" class="form-input__icon--prefix">
        <i :class="prefixIcon" class="form-input__icon"></i>
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
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      >

      <!-- サフィックスアイコン -->
      <div v-if="suffixIcon" class="form-input__icon--suffix">
        <i :class="suffixIcon" class="form-input__icon"></i>
      </div>

      <!-- クリアボタン -->
      <button
        v-if="clearable && modelValue"
        type="button"
        class="form-input__clear"
        @click="clearValue"
      >
        <i class="fas fa-times form-input__clear-icon"></i>
      </button>
    </div>

    <!-- ヘルプテキスト -->
    <p v-if="helpText" class="form-input__help">
      {{ helpText }}
    </p>

    <!-- エラーメッセージ -->
    <p v-if="error" class="form-input__error">
      <i class="fas fa-exclamation-circle form-input__error-icon"></i>
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
      const classes = ['form-input__label']
      if (this.disabled) {
        classes.push('form-input__label--disabled')
      } else {
        classes.push('form-input__label--normal')
      }
      return classes.join(' ')
    },

    inputClasses() {
      const classes = ['form-input__field']

      // 基本スタイル
      if (this.prefixIcon) classes.push('form-input__field--with-prefix')
      if (this.suffixIcon || this.clearable) classes.push('form-input__field--with-suffix')

      // テキストエリア用
      if (this.type === 'textarea') classes.push('form-input__textarea')

      // 状態に応じたスタイル
      if (this.error) {
        classes.push('form-input__field--error')
      } else if (this.focused) {
        classes.push('form-input__field--focused')
      } else {
        classes.push('form-input__field--normal')
      }

      if (this.disabled) {
        classes.push('form-input__field--disabled')
      } else if (this.readonly) {
        classes.push('form-input__field--readonly')
      }

      if (this.loading) {
        classes.push('form-input__field--loading')
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

<style src="~/assets/css/components/FormInput.css"></style>