<template>
  <div
    :class="cardClasses"
    @click="handleClick"
  >
    <div class="stat-card__content">
      <!-- アイコン -->
      <div v-if="icon" :class="iconClasses">
        <i :class="icon" class="stat-card__icon"></i>
      </div>

      <!-- コンテンツ -->
      <div class="stat-card__data">
        <!-- 値 -->
        <div class="stat-card__value-container">
          <div :class="valueClasses">
            {{ displayValue }}
          </div>
          <div v-if="unit" class="stat-card__unit">
            {{ unit }}
          </div>
        </div>

        <!-- ラベル -->
        <div class="stat-card__label">
          {{ label }}
        </div>

        <!-- 変化量（オプション） -->
        <div v-if="change !== null" class="stat-card__change">
          <i :class="changeIcon" class="stat-card__change-icon"></i>
          <span :class="changeClasses">
            {{ changeText }}
          </span>
          <span v-if="changePeriod" class="stat-card__change-period">
            {{ changePeriod }}
          </span>
        </div>
      </div>

      <!-- 追加コンテンツ -->
      <div v-if="$slots.extra" class="stat-card__extra">
        <slot name="extra"></slot>
      </div>
    </div>

    <!-- ボトムコンテンツ -->
    <div v-if="$slots.bottom" class="stat-card__bottom">
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
      const classes = ['stat-card']

      // ボーダー
      if (this.borderPosition === 'left') {
        classes.push('stat-card--border-left')
        classes.push(`stat-card--${this.color}`)
      } else if (this.borderPosition === 'top') {
        classes.push('stat-card--border-top')
        classes.push(`stat-card--${this.color}`)
      }

      // クリック可能
      if (this.clickable) {
        classes.push('stat-card--clickable')
      }

      // ローディング
      if (this.loading) {
        classes.push('stat-card--loading')
      }

      return classes.join(' ')
    },

    iconClasses() {
      return `stat-card__icon-container stat-card__icon-container--${this.color}`
    },

    valueClasses() {
      const classes = ['stat-card__value']
      if (this.color === 'gray') {
        classes.push('stat-card__value--gray')
      } else {
        classes.push('stat-card__value--normal')
      }
      return classes.join(' ')
    },

    changeIcon() {
      if (this.change === null) return ''
      return this.change > 0 ? 'fas fa-arrow-up' : this.change < 0 ? 'fas fa-arrow-down' : 'fas fa-minus'
    },

    changeClasses() {
      const classes = ['stat-card__change-value']
      if (this.change === null) return ''

      if (this.change > 0) {
        classes.push('stat-card__change-value--positive')
      } else if (this.change < 0) {
        classes.push('stat-card__change-value--negative')
      } else {
        classes.push('stat-card__change-value--neutral')
      }

      return classes.join(' ')
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

<style src="~/assets/css/components/StatCard.css"></style>
