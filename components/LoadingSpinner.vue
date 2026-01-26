<template>
  <div v-if="show" :class="containerClasses">
    <!-- スピナー -->
    <div :class="spinnerClasses" />

    <!-- メッセージ -->
    <div v-if="message" :class="messageClasses">
      {{ message }}
    </div>

    <!-- 追加コンテンツ -->
    <div v-if="$slots.default" class="spinner-slot">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingSpinner',

  props: {
    show: {
      type: Boolean,
      default: true
    },
    message: {
      type: String,
      default: '読み込み中...'
    },
    size: {
      type: String,
      default: 'medium',
      validator: value => ['small', 'medium', 'large'].includes(value)
    },
    color: {
      type: String,
      default: 'blue',
      validator: value => ['blue', 'green', 'yellow', 'red', 'purple', 'gray', 'white'].includes(value)
    },
    overlay: {
      type: Boolean,
      default: false
    },
    center: {
      type: Boolean,
      default: true
    },
    inline: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    containerClasses () {
      const classes = ['spinner-container']

      if (this.overlay) {
        classes.push('spinner-container--overlay')
      }

      if (this.center) {
        classes.push('spinner-container--center')
        if (!this.inline) {
          classes.push('spinner-container--center-block')
        }
      }

      if (this.inline) {
        classes.push('spinner-container--inline')
      } else {
        classes.push('spinner-container--block')
      }

      return classes.join(' ')
    },

    spinnerClasses () {
      const classes = ['spinner']
      classes.push(`spinner--${this.size}`)
      classes.push(`spinner--${this.color}`)
      return classes.join(' ')
    },

    messageClasses () {
      const classes = ['spinner-message']

      if (this.inline) {
        classes.push('spinner-message--inline')
      } else {
        classes.push('spinner-message--block')
      }

      if (this.overlay) {
        classes.push('spinner-message--overlay')
      }

      return classes.join(' ')
    }
  }
}
</script>

<style src="~/assets/css/components/LoadingSpinner.css"></style>
