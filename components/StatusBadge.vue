<template>
  <span :class="badgeClasses" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
    <i v-if="icon" :class="icon" class="mr-1"></i>
    {{ statusText }}
  </span>
</template>

<script>
export default {
  name: 'StatusBadge',
  
  props: {
    status: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: 'attendance', // 'attendance', 'request', 'user'
      validator: value => ['attendance', 'request', 'user'].includes(value)
    },
    showIcon: {
      type: Boolean,
      default: false
    }
  },
  
  computed: {
    statusConfig() {
      const configs = {
        attendance: {
          normal: {
            text: '正常',
            classes: 'bg-green-100 text-green-800',
            icon: 'fas fa-check-circle'
          },
          late: {
            text: '遅刻',
            classes: 'bg-yellow-100 text-yellow-800',
            icon: 'fas fa-clock'
          },
          early_leave: {
            text: '早退',
            classes: 'bg-orange-100 text-orange-800',
            icon: 'fas fa-door-open'
          },
          absent: {
            text: '欠席',
            classes: 'bg-red-100 text-red-800',
            icon: 'fas fa-times-circle'
          },
          pending: {
            text: '承認待ち',
            classes: 'bg-blue-100 text-blue-800',
            icon: 'fas fa-hourglass-half'
          },
          working: {
            text: '勤務中',
            classes: 'bg-green-100 text-green-800',
            icon: 'fas fa-user-clock'
          },
          off: {
            text: '勤務時間外',
            classes: 'bg-gray-100 text-gray-800',
            icon: 'fas fa-moon'
          }
        },
        request: {
          pending: {
            text: '承認待ち',
            classes: 'bg-yellow-100 text-yellow-800',
            icon: 'fas fa-hourglass-half'
          },
          approved: {
            text: '承認済み',
            classes: 'bg-green-100 text-green-800',
            icon: 'fas fa-check-circle'
          },
          rejected: {
            text: '却下',
            classes: 'bg-red-100 text-red-800',
            icon: 'fas fa-times-circle'
          },
          canceled: {
            text: 'キャンセル',
            classes: 'bg-gray-100 text-gray-800',
            icon: 'fas fa-ban'
          }
        },
        user: {
          active: {
            text: 'アクティブ',
            classes: 'bg-green-100 text-green-800',
            icon: 'fas fa-user-check'
          },
          inactive: {
            text: '無効',
            classes: 'bg-red-100 text-red-800',
            icon: 'fas fa-user-times'
          },
          pending: {
            text: '認証待ち',
            classes: 'bg-yellow-100 text-yellow-800',
            icon: 'fas fa-user-clock'
          },
          admin: {
            text: '管理者',
            classes: 'bg-purple-100 text-purple-800',
            icon: 'fas fa-user-shield'
          },
          user: {
            text: '一般ユーザー',
            classes: 'bg-blue-100 text-blue-800',
            icon: 'fas fa-user'
          }
        }
      }
      
      return configs[this.type]?.[this.status] || {
        text: this.status,
        classes: 'bg-gray-100 text-gray-800',
        icon: 'fas fa-question-circle'
      }
    },
    
    statusText() {
      return this.statusConfig.text
    },
    
    badgeClasses() {
      return this.statusConfig.classes
    },
    
    icon() {
      return this.showIcon ? this.statusConfig.icon : null
    }
  }
}
</script>