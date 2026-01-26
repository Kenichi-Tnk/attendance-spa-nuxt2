<template>
  <span :class="badgeClasses">
    <i v-if="icon" :class="icon" class="status-badge__icon" />
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
    statusConfig () {
      const configs = {
        attendance: {
          working: {
            text: '勤務中',
            classes: 'status-badge--attendance-working',
            icon: 'fas fa-user-clock'
          },
          completed: {
            text: '勤務完了',
            classes: 'status-badge--attendance-completed',
            icon: 'fas fa-check-circle'
          },
          absent: {
            text: '勤務外',
            classes: 'status-badge--attendance-absent',
            icon: 'fas fa-user-times'
          }
        },
        request: {
          pending: {
            text: '承認待ち',
            classes: 'status-badge--request-pending',
            icon: 'fas fa-hourglass-half'
          },
          approved: {
            text: '承認済',
            classes: 'status-badge--request-approved',
            icon: 'fas fa-check-circle'
          },
          rejected: {
            text: '却下',
            classes: 'status-badge--request-rejected',
            icon: 'fas fa-times-circle'
          },
          canceled: {
            text: 'キャンセル',
            classes: 'status-badge--request-canceled',
            icon: 'fas fa-ban'
          }
        },
        user: {
          active: {
            text: 'アクティブ',
            classes: 'status-badge--user-active',
            icon: 'fas fa-user-check'
          },
          inactive: {
            text: '無効',
            classes: 'status-badge--user-inactive',
            icon: 'fas fa-user-times'
          },
          pending: {
            text: '認証待ち',
            classes: 'status-badge--user-pending',
            icon: 'fas fa-user-clock'
          },
          admin: {
            text: '管理者',
            classes: 'status-badge--user-admin',
            icon: 'fas fa-user-shield'
          },
          user: {
            text: '一般ユーザー',
            classes: 'status-badge--user-user',
            icon: 'fas fa-user'
          }
        }
      }

      return configs[this.type]?.[this.status] || {
        text: this.status,
        classes: 'status-badge--default',
        icon: 'fas fa-question-circle'
      }
    },

    statusText () {
      return this.statusConfig.text
    },

    badgeClasses () {
      const classes = ['status-badge']
      classes.push(this.statusConfig.classes)
      return classes.join(' ')
    },

    icon () {
      return this.showIcon ? this.statusConfig.icon : null
    }
  }
}
</script>

<style src="~/assets/css/components/StatusBadge.css"></style>
