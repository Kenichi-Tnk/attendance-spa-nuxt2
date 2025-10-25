&lt;template&gt;
  &lt;div v-if="show" :class="containerClasses"&gt;
    &lt;!-- スピナー --&gt;
    &lt;div :class="spinnerClasses"&gt;&lt;/div&gt;
    
    &lt;!-- メッセージ --&gt;
    &lt;div v-if="message" :class="messageClasses"&gt;
      {{ message }}
    &lt;/div&gt;
    
    &lt;!-- 追加コンテンツ --&gt;
    &lt;div v-if="$slots.default" class="mt-4"&gt;
      &lt;slot&gt;&lt;/slot&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
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
      validator: value =&gt; ['small', 'medium', 'large'].includes(value)
    },
    color: {
      type: String,
      default: 'blue',
      validator: value =&gt; ['blue', 'green', 'yellow', 'red', 'purple', 'gray', 'white'].includes(value)
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
    containerClasses() {
      const classes = []
      
      if (this.overlay) {
        classes.push('fixed inset-0 bg-black bg-opacity-50 z-50')
      }
      
      if (this.center) {
        classes.push('flex items-center justify-center')
        if (!this.inline) {
          classes.push('min-h-32')
        }
      }
      
      if (this.inline) {
        classes.push('inline-flex items-center')
      } else {
        classes.push('flex flex-col')
      }
      
      return classes.join(' ')
    },
    
    spinnerClasses() {
      const classes = ['animate-spin rounded-full border-2 border-transparent']
      
      // サイズ
      const sizes = {
        small: 'h-4 w-4',
        medium: 'h-8 w-8',
        large: 'h-12 w-12'
      }
      classes.push(sizes[this.size])
      
      // カラー
      const colors = {
        blue: 'border-t-blue-600 border-r-blue-600',
        green: 'border-t-green-600 border-r-green-600',
        yellow: 'border-t-yellow-600 border-r-yellow-600',
        red: 'border-t-red-600 border-r-red-600',
        purple: 'border-t-purple-600 border-r-purple-600',
        gray: 'border-t-gray-600 border-r-gray-600',
        white: 'border-t-white border-r-white'
      }
      classes.push(colors[this.color])
      
      return classes.join(' ')
    },
    
    messageClasses() {
      const classes = ['text-sm']
      
      if (this.inline) {
        classes.push('ml-3')
      } else {
        classes.push('mt-3 text-center')
      }
      
      // オーバーレイ時は白文字
      if (this.overlay) {
        classes.push('text-white')
      } else {
        classes.push('text-gray-600')
      }
      
      return classes.join(' ')
    }
  }
}
&lt;/script&gt;