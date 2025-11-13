<template>
    <div class="correction-request-new">
        <PageHeader
        title="新規修正申請"
        :breadcrumbs="breadcrumbs"
        />

        <div class="correction-request-new__container">
            <form @submit.prevent="submitRequest" class="correction-request-form">
                <!-- 対象日選択 -->
                <div class="form-group">
                    <label for="date" class="form-label">対象日 <span class="required">*</span></label>
                    <!-- 一時的に直接的なinput要素でテスト -->
                    <input
                        id="date"
                        v-model="form.date"
                        type="date"
                        :max="today"
                        class="form-input"
                        @input="onDateInput"
                        @blur="onDateBlur"
                        @focus="onDateFocus"
                    />
                    <div v-if="errors.date" class="error-message">{{ errors.date }}</div>
                </div>

                <!-- 現在の勤怠データ表示 -->
                <div v-if="currentAttendance" class="current-attendance">
                    <h3 class="current-attendance__title">現在の勤怠データ</h3>
                    <div class="current-attendance__data">
                        <div class="attendance-item">
                            <span class="label">出勤時刻:</span>
                            <span class="value">{{ formatTime(currentAttendance.check_in) }}</span>
                        </div>
                        <div class="attendance-item">
                            <span class="label">退勤時刻:</span>
                            <span class="value">{{ formatTime(currentAttendance.check_out) || '未退勤' }}</span>
                        </div>
                        <div class="attendance-item">
                            <span class="label">労働時間:</span>
                            <span class="value">{{ calculateWorkHours(currentAttendance) }}</span>
                        </div>
                    </div>
                </div>

                <!-- 修正内容入力 -->
                <div class="form-section">
                    <h3 class="section-title">修正内容</h3>

                    <div class="form-group">
                        <label for="check_in" class="form-label">出勤時刻 <span class="required">*</span></label>
                        <!-- 一時的に直接的なinput要素でテスト -->
                        <input
                            id="check_in"
                            v-model="form.check_in"
                            type="time"
                            class="form-input"
                            @input="onTimeInput('check_in', $event)"
                            @blur="onTimeBlur('check_in')"
                            @focus="onTimeFocus('check_in')"
                        />
                        <div v-if="errors.check_in" class="error-message">{{ errors.check_in }}</div>
                    </div>

                    <div class="form-group">
                        <label for="check_out" class="form-label">退勤時刻</label>
                        <!-- 一時的に直接的なinput要素でテスト -->
                        <input
                            id="check_out"
                            v-model="form.check_out"
                            type="time"
                            class="form-input"
                            @input="onTimeInput('check_out', $event)"
                            @blur="onTimeBlur('check_out')"
                            @focus="onTimeFocus('check_out')"
                        />
                        <div v-if="errors.check_out" class="error-message">{{ errors.check_out }}</div>
                    </div>
                </div>

                <!-- 休憩時間修正 -->
                <div class="form-section">
                    <h3 class="section-title">休憩時間修正</h3>

                    <div v-for="(rest, index) in form.rests" :key="index" class="rest-item">
                        <div class="rest-item__fields">
                            <div class="form-group">
                                <label class="form-label">休憩開始</label>
                                <input
                                    type="time"
                                    v-model="rest.rest_start"
                                    class="form-input"
                                    :class="{ 'is-invalid': errors[`rests.${index}.rest_start`] }"
                                    @input="onRestInput('rest_start', index, $event)"
                                    @focus="onRestFocus('rest_start', index)"
                                    @blur="onRestBlur('rest_start', index)"
                                />
                                <div v-if="errors[`rests.${index}.rest_start`]" class="invalid-feedback">
                                    {{ errors[`rests.${index}.rest_start`][0] }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">休憩終了</label>
                                <input
                                    type="time"
                                    v-model="rest.rest_end"
                                    class="form-input"
                                    :class="{ 'is-invalid': errors[`rests.${index}.rest_end`] }"
                                    @input="onRestInput('rest_end', index, $event)"
                                    @focus="onRestFocus('rest_end', index)"
                                    @blur="onRestBlur('rest_end', index)"
                                />
                                <div v-if="errors[`rests.${index}.rest_end`]" class="invalid-feedback">
                                    {{ errors[`rests.${index}.rest_end`][0] }}
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="removeRest(index)"
                                class="rest-item__remove-btn"
                                title="この休憩時間を削除"
                            >
                                <i class="fas fa-times"></i>
                                <span class="btn-text">削除</span>
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addRest"
                        class="add-rest-btn"
                    >
                        <i class="fas fa-plus"></i>
                        休憩時間を追加
                    </button>
                </div>

                <!-- 申請理由 -->
                <div class="form-group">
                    <label for="reason" class="form-label">申請理由 <span class="required">*</span></label>
                    <textarea
                        id="reason"
                        v-model="form.reason"
                        :class="['form-textarea', { 'error': errors.reason }]"
                        placeholder="修正が必要な理由を詳しく記入してください"
                        rows="4"
                        maxlength="500"
                    ></textarea>
                    <div v-if="errors.reason" class="error-message">{{ errors.reason }}</div>
                    <div class="char-count">{{ form.reason.length }}/500</div>
                </div>

                <!-- ボタン -->
                <div class="form-actions">
                    <nuxt-link
                    to="/correction-requests"
                    class="btn btn--secondary"
                    >
                        キャンセル
                    </nuxt-link>
                    <!-- デバッグボタン（開発環境でのみ表示） -->
                    <button
                    v-if="$config.dev"
                    type="button"
                    @click="debugFormData"
                    class="btn btn--secondary"
                    style="margin: 0 8px;"
                    >
                        デバッグ情報
                    </button>
                    <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="btn btn--primary"
                    >
                        <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
                        <span>{{ isSubmitting ? '申請中...' : '申請する' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import PageHeader from '~/components/PageHeader.vue'

export default {
    name: 'CorrectionRequestNew',
    components: {
        PageHeader
    },
    middleware: ['auth', 'verified'],

    data() {
        return {
            form: {
                date: '',
                check_in: '',
                check_out: '',
                reason: '',
                rests: []
            },
            currentAttendance: null,
            errors: {},
            isSubmitting: false,
            isLoadingAttendance: false,
            isUpdatingForm: false // フォーム更新中フラグ
        }
    },

    computed: {
        breadcrumbs() {
            return [
                { text: 'ダッシュボード', to: '/dashboard' },
                { text: '修正申請', to: '/correction-requests' },
                { text: '新規申請' }
            ]
        },

        today() {
            return new Date().toISOString().split('T')[0]
        }
    },
    
    mounted() {
        // コンポーネント初期化時の処理
    },

    watch: {
        'form.date'(newDate, oldDate) {
            if (this.isUpdatingForm) {
                return
            }
            
            if (newDate && newDate !== oldDate) {
                setTimeout(() => {
                    this.loadAttendanceData()
                }, 300)
            }
        },
        
                form: {
            handler() {
                // フォーム変更の監視処理
            },
            deep: true
        }
    },

    methods: {
        async loadAttendanceData() {
            if (!this.form.date) {
                this.currentAttendance = null
                return
            }

            try {
                this.isLoadingAttendance = true

                const response = await this.$axios.$get('/api/attendance')

                const attendanceData = response.data.find(item => {
                    const itemDate = item.date.split('T')[0]
                    return itemDate === this.form.date
                })

                if (attendanceData) {
                    this.currentAttendance = attendanceData

                    this.isUpdatingForm = true
                    await this.$nextTick()
                    
                    // 現在の値が空の場合のみフォームにセット
                    if (!this.form.check_in) {
                        this.form.check_in = this.formatTime(attendanceData.check_in)
                    }
                    if (!this.form.check_out) {
                        this.form.check_out = this.formatTime(attendanceData.check_out)
                    }

                    // 休憩データを更新
                    if (attendanceData.rests && attendanceData.rests.length > 0 && this.form.rests.length === 0) {
                        this.form.rests = attendanceData.rests.map(rest => ({
                        rest_start: this.formatTime(rest.rest_start),
                        rest_end: this.formatTime(rest.rest_end)
                        }))
                    }
                    
                    await this.$nextTick()
                    this.isUpdatingForm = false
                } else {
                    this.currentAttendance = null
                    if (this.$toast) {
                        this.$toast.warning('指定した日の勤怠データが見つかりません。手動で入力してください。')
                    }
                }
            } catch (error) {
                console.error('勤怠データ取得エラー:', error)
                this.currentAttendance = null
                if (this.$toast) {
                    this.$toast.error('勤怠データの取得に失敗しました')
                }
            } finally {
                this.isLoadingAttendance = false
            }
        },

        async submitRequest() {
            try {
                this.isSubmitting = true
                this.errors = {}

                const requestData = {
                    date: this.form.date,
                    check_in: this.form.check_in,
                    check_out: this.form.check_out || null,
                    reason: this.form.reason,
                    rests: this.form.rests.filter(rest => rest.rest_start && rest.rest_end)
                }

                const response = await this.$axios.$post('/api/correction-requests', requestData)

                if (this.$toast) {
                    this.$toast.success('修正申請を提出しました')
                }

                this.$router.push('/correction-requests')
            } catch (error) {
                console.error('申請提出エラー:', error)

                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors
                }

                const message = error.response?.data?.message || '申請の提出に失敗しました'
                if (this.$toast) {
                    this.$toast.error(message)
                }
            } finally {
                this.isSubmitting = false
            }
        },

        addRest() {
            this.form.rests.push({
                rest_start: '',
                rest_end: ''
            })
        },

        removeRest(index) {
            this.form.rests.splice(index, 1)
        },

        clearForm() {
                        // 時間関連のフィールドをクリア
            this.form.check_in = ''
            this.form.check_out = ''
            this.form.rests = []
        },

        clearAllForm() {
            // フォームを完全にリセット
        },

        resetFormCompletely() {
            // 完全にフォームをリセット
            this.form = {
                date: '',
                check_in: '',
                check_out: '',
                reason: '',
                rests: []
            }
            this.currentAttendance = null
            this.errors = {}
        },

    formatTime(timeString) {
      if (!timeString) return ''
      // HH:MM:SS形式をHH:MM形式に変換
      return timeString.substring(0, 5)
    },
    
    formatDate(dateInput) {
      if (!dateInput) return ''
      
      // 既にYYYY-MM-DD形式の場合はそのまま返す
      if (/^\d{4}-\d{2}-\d{2}$/.test(dateInput)) {
        return dateInput
      }
      
      // Date オブジェクトまたは他の形式からYYYY-MM-DD形式に変換
      const date = new Date(dateInput)
      if (isNaN(date.getTime())) {
        return ''
      }
      
      return date.toISOString().split('T')[0]
    },        calculateWorkHours(attendance) {
            if (!attendance.check_in || !attendance.check_out) {
                return '−'
            }

            const dateStr = attendance.date.split('T')[0]
            const startTime = new Date(`${dateStr}T${attendance.check_in}`)
            const endTime = new Date(`${dateStr}T${attendance.check_out}`)

            if (isNaN(startTime.getTime()) || isNaN(endTime.getTime())) {
                return '−'
            }

            let workMilliseconds = endTime - startTime

            // 休憩時間を差し引く
            if (attendance.rests && attendance.rests.length > 0) {
                const totalBreakMilliseconds = attendance.rests.reduce((total, rest) => {
                    if (rest.rest_start && rest.rest_end) {
                        const restStart = new Date(`${dateStr}T${rest.rest_start}`)
                        const restEnd = new Date(`${dateStr}T${rest.rest_end}`)

                        if (!isNaN(restStart.getTime()) && !isNaN(restEnd.getTime())) {
                            return total + (restEnd - restStart)
                        }
                    }
                    return total
                }, 0)

                workMilliseconds -= totalBreakMilliseconds
            }

            if (workMilliseconds <= 0) return '0:00'

            const hours = Math.floor(workMilliseconds / (1000 * 60 * 60))
            const minutes = Math.floor((workMilliseconds % (1000 * 60 * 60)) / (1000 * 60))

            return `${hours}:${String(minutes).padStart(2, '0')}`
        },


    }
}
</script>

<style src="@/assets/css/pages/correction-requests-new.css" scoped></style>