<template>
  <div class="correction-request-new">
    <div class="correction-request-new__page-header">
      <h1 class="correction-request-new__title">
        修正申請
      </h1>
      <p class="correction-request-new__subtitle">
        勤怠情報の修正を申請できます
      </p>
    </div>

    <div class="correction-request-new__container">
      <form class="correction-request-form" @submit.prevent="submitRequest">
        <!-- 対象日選択 -->
        <div class="form-group">
          <label
            for="date"
            class="form-label"
          >対象日 <span class="required">*</span></label>
          <input
            id="date"
            v-model="form.date"
            type="date"
            :max="today"
            :readonly="isDateFromQuery"
            :class="['form-input', { 'form-input--readonly': isDateFromQuery }]"
            @blur="onDateBlur"
            @focus="onDateFocus"
          >
          <div v-if="isDateFromQuery" class="form-help-text">
            <i class="fas fa-info-circle" />
            勤怠一覧から選択された日付です
          </div>
          <div v-if="errors.date" class="error-message">
            {{ getErrorMessage(errors.date) }}
          </div>
        </div>

        <!-- 現在の勤怠データ表示 -->
        <div v-if="currentAttendance" class="current-attendance">
          <h3 class="current-attendance__title">
            現在の勤怠データ
          </h3>
          <div class="current-attendance__data">
            <div class="attendance-item">
              <span class="label">出勤時刻:</span>
              <span class="value">{{
                formatTime(currentAttendance.check_in)
              }}</span>
            </div>
            <div class="attendance-item">
              <span class="label">退勤時刻:</span>
              <span class="value">{{
                formatTime(currentAttendance.check_out) || "未退勤"
              }}</span>
            </div>
            <div class="attendance-item">
              <span class="label">労働時間:</span>
              <span class="value">{{
                calculateWorkHours(currentAttendance)
              }}</span>
            </div>
          </div>
        </div>

        <!-- 承認待ち通知（目立つ表示） -->
        <div v-if="hasPendingRequest" class="pending-request-alert">
          <div class="pending-request-alert__icon">
            <i class="fas fa-hourglass-half" />
          </div>
          <div class="pending-request-alert__content">
            <h3 class="pending-request-alert__title">
              承認待ちの申請があります
            </h3>
            <p class="pending-request-alert__message">
              この日付は既に修正申請が提出されており、管理者の承認待ちです。<br>
              承認または却下されるまで、新たな申請はできません。
            </p>
            <div class="pending-request-alert__info">
              <span class="info-label">申請ID:</span>
              <span class="info-value">#{{ pendingRequest.id }}</span>
              <span class="info-separator">|</span>
              <span class="info-label">申請日時:</span>
              <span class="info-value">{{
                formatDateTime(pendingRequest.created_at)
              }}</span>
            </div>
            <div class="pending-request-alert__actions">
              <nuxt-link
                :to="`/correction-requests/${pendingRequest.id}`"
                class="btn btn--secondary btn--sm"
              >
                <i class="fas fa-eye" />
                申請内容を確認
              </nuxt-link>
              <nuxt-link
                to="/correction-requests"
                class="btn btn--outline btn--sm"
              >
                <i class="fas fa-list" />
                申請一覧へ戻る
              </nuxt-link>
            </div>
          </div>
        </div>

        <!-- 修正内容入力 -->
        <div class="form-section">
          <h3 class="section-title">
            修正内容
          </h3>

          <div class="form-group">
            <label
              for="check_in"
              class="form-label"
            >出勤時刻 <span class="required">*</span></label>
            <input
              id="check_in"
              v-model="form.check_in"
              type="time"
              class="form-input"
              :disabled="hasPendingRequest"
              :class="{ 'form-input--disabled': hasPendingRequest }"
              @blur="onTimeBlur('check_in')"
              @focus="onTimeFocus('check_in')"
            >
            <div v-if="errors.check_in" class="error-message">
              {{ getErrorMessage(errors.check_in) }}
            </div>
          </div>

          <div class="form-group">
            <label for="check_out" class="form-label">退勤時刻</label>
            <input
              id="check_out"
              v-model="form.check_out"
              type="time"
              class="form-input"
              :disabled="hasPendingRequest"
              :class="{ 'form-input--disabled': hasPendingRequest }"
              @blur="onTimeBlur('check_out')"
              @focus="onTimeFocus('check_out')"
            >
            <div v-if="errors.check_out" class="error-message">
              {{ getErrorMessage(errors.check_out) }}
            </div>
          </div>
        </div>

        <!-- 休憩時間修正 -->
        <div class="form-section">
          <h3 class="section-title">
            休憩時間修正
          </h3>

          <div
            v-for="(rest, index) in form.rests"
            :key="index"
            class="rest-item"
          >
            <div class="rest-item__fields">
              <div class="form-group">
                <label class="form-label">休憩開始</label>
                <input
                  v-model="rest.rest_start"
                  type="time"
                  class="form-input"
                  :class="{ 'is-invalid': errors[`rests.${index}.rest_start`] }"
                  @focus="onRestFocus('rest_start', index)"
                  @blur="onRestBlur('rest_start', index)"
                >
                <div
                  v-if="errors[`rests.${index}.rest_start`]"
                  class="error-message"
                >
                  {{ getErrorMessage(errors[`rests.${index}.rest_start`]) }}
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">休憩終了</label>
                <input
                  v-model="rest.rest_end"
                  type="time"
                  class="form-input"
                  :class="{ 'is-invalid': errors[`rests.${index}.rest_end`] }"
                  @focus="onRestFocus('rest_end', index)"
                  @blur="onRestBlur('rest_end', index)"
                >
                <div
                  v-if="errors[`rests.${index}.rest_end`]"
                  class="error-message"
                >
                  {{ getErrorMessage(errors[`rests.${index}.rest_end`]) }}
                </div>
              </div>
              <button
                type="button"
                class="rest-item__remove-btn"
                title="この休憩時間を削除"
                @click="removeRest(index)"
              >
                <i class="fas fa-times" />
                <span class="btn-text">削除</span>
              </button>
            </div>
          </div>

          <button
            type="button"
            class="add-rest-btn"
            :disabled="hasPendingRequest"
            @click="addRest"
          >
            <i class="fas fa-plus" />
            休憩時間を追加
          </button>
        </div>

        <!-- 申請理由 -->
        <div class="form-group">
          <label
            for="reason"
            class="form-label"
          >申請理由 <span class="required">*</span></label>
          <textarea
            id="reason"
            v-model="form.reason"
            :class="[
              'form-textarea',
              {
                error: errors.reason,
                'form-textarea--disabled': hasPendingRequest,
              },
            ]"
            :disabled="hasPendingRequest"
            placeholder="修正が必要な理由を詳しく記入してください"
            rows="4"
            maxlength="500"
          />
          <div v-if="errors.reason" class="error-message">
            {{ getErrorMessage(errors.reason) }}
          </div>
          <div v-if="!hasPendingRequest" class="char-count">
            {{ form.reason.length }}/500
          </div>
        </div>

        <!-- ボタン -->
        <div class="form-actions">
          <nuxt-link to="/attendance" class="btn btn--secondary">
            <i class="fas fa-arrow-left" />
            勤怠一覧へ戻る
          </nuxt-link>
          <!-- デバッグボタン（開発環境でのみ表示） -->
          <button
            v-if="$config.dev"
            type="button"
            class="btn btn--secondary"
            style="margin: 0 8px"
            @click="debugFormData"
          >
            デバッグ情報
          </button>
          <button
            v-if="!hasPendingRequest"
            type="submit"
            :disabled="isSubmitting"
            class="btn btn--primary"
          >
            <i v-if="isSubmitting" class="fas fa-spinner fa-spin" />
            <span>{{ isSubmitting ? "申請中..." : "申請する" }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CorrectionRequestNew',
  middleware: ['auth', 'verified'],

  data () {
    return {
      form: {
        date: '',
        check_in: '',
        check_out: '',
        reason: '',
        rests: []
      },
      currentAttendance: null,
      pendingRequest: null, // 申請中の修正依頼
      errors: {},
      isSubmitting: false,
      isLoadingAttendance: false,
      isUpdatingForm: false, // フォーム更新中フラグ
      isDateFromQuery: false // クエリパラメータから日付が渡されたかどうか
    }
  },

  computed: {
    today () {
      return new Date().toISOString().split('T')[0]
    },

    hasPendingRequest () {
      return this.pendingRequest && this.pendingRequest.status === 'pending'
    }
  },

  watch: {
    'form.date' (newDate, oldDate) {
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
      handler () {
        // フォーム変更の監視処理
      },
      deep: true
    }
  },

  mounted () {
    // クエリパラメータから日付を取得
    const dateFromQuery = this.$route.query.date

    if (dateFromQuery) {
      // 日付をフォームに設定
      this.form.date = dateFromQuery
      this.isDateFromQuery = true
      // データを自動的にロード
      this.$nextTick(() => {
        this.loadAttendanceData()
      })
    } else {
      // 日付パラメータがない場合は勤怠一覧へリダイレクト
      this.$router.replace('/attendance')
    }
  },

  methods: {
    async loadAttendanceData () {
      if (!this.form.date) {
        this.currentAttendance = null
        this.pendingRequest = null
        return
      }

      try {
        this.isLoadingAttendance = true

        // 勤怠データと修正申請データを並行で取得
        const [attendanceResponse, requestsResponse] = await Promise.all([
          this.$axios.$get('/api/attendance'),
          this.$axios.$get('/api/correction-requests')
        ])

        // 指定日の勤怠データを検索
        const attendanceData = attendanceResponse.data.find((item) => {
          const itemDate = item.date.split('T')[0]
          return itemDate === this.form.date
        })

        // 修正申請データの取得（ページネーション対応）
        const requestsData = requestsResponse.data || requestsResponse
        const requests = Array.isArray(requestsData)
          ? requestsData
          : requestsData.data || []

        // 指定日の申請中の修正依頼を検索
        const pendingRequestData = requests.find((item) => {
          const itemDate = item.date ? item.date.split('T')[0] : null
          const isPending = item.status === 'pending'
          const isMatchDate = itemDate === this.form.date

          return isMatchDate && isPending
        })

        this.pendingRequest = pendingRequestData || null

        if (attendanceData) {
          this.currentAttendance = attendanceData

          // 申請中の場合はフォームをクリア
          if (this.hasPendingRequest) {
            this.isUpdatingForm = true
            await this.$nextTick()

            // フォームの入力フィールドを強制的にクリア
            this.form.check_in = ''
            this.form.check_out = ''
            this.form.reason = ''
            this.form.rests = []

            await this.$nextTick()
            this.isUpdatingForm = false
          } else {
            // 申請中でない場合のみフォームにセット
            this.isUpdatingForm = true
            await this.$nextTick()

            // フォームに勤怠データをセット
            this.form.check_in = this.formatTime(attendanceData.check_in)
            this.form.check_out = this.formatTime(attendanceData.check_out)

            // 休憩データを更新
            if (attendanceData.rests && attendanceData.rests.length > 0) {
              this.form.rests = attendanceData.rests.map(rest => ({
                rest_start: this.formatTime(rest.rest_start),
                rest_end: this.formatTime(rest.rest_end)
              }))
            } else {
              this.form.rests = []
            }

            await this.$nextTick()
            this.isUpdatingForm = false
          }
        } else {
          this.currentAttendance = null
          if (this.$toast && !this.hasPendingRequest) {
            this.$toast.warning(
              '指定した日の勤怠データが見つかりません。手動で入力してください。'
            )
          }
        }
      } catch (error) {
        console.error('データ取得エラー:', error)
        this.currentAttendance = null
        this.pendingRequest = null
        if (this.$toast) {
          this.$toast.error('データの取得に失敗しました')
        }
      } finally {
        this.isLoadingAttendance = false
      }
    },

    async submitRequest () {
      try {
        this.isSubmitting = true
        this.errors = {}

        // フロントエンドバリデーション
        const validationErrors = this.validateForm()
        if (Object.keys(validationErrors).length > 0) {
          this.errors = validationErrors
          this.isSubmitting = false
          if (this.$toast) {
            this.$toast.error('入力内容に誤りがあります')
          }
          return
        }

        const requestData = {
          date: this.form.date,
          check_in: this.form.check_in,
          check_out: this.form.check_out || null,
          reason: this.form.reason,
          rests: this.form.rests.filter(
            rest => rest.rest_start && rest.rest_end
          )
        }

        const response = await this.$axios.$post(
          '/api/correction-requests',
          requestData
        )

        // 申請成功後、pendingRequestを設定してボタンを非表示にする
        this.pendingRequest = {
          ...response,
          status: 'pending'
        }

        // フォームの入力フィールドをクリア（日付は残す）
        this.form.check_in = ''
        this.form.check_out = ''
        this.form.reason = ''
        this.form.rests = []

        if (this.$toast) {
          this.$toast.success('修正申請を提出しました')
        }

        // 3秒後に勤怠一覧画面へ遷移
        setTimeout(() => {
          this.$router.push('/attendance')
        }, 3000)
      } catch (error) {
        console.error('申請提出エラー:', error)

        if (error.response?.data?.errors) {
          // バリデーションエラーを日本語化
          const apiErrors = error.response.data.errors
          this.errors = {}

          for (const [field, messages] of Object.entries(apiErrors)) {
            const translatedMessages = messages.map((msg) => {
              // 日付関連
              if (msg.includes('date') && msg.includes('required')) {
                return '対象日は必須項目です'
              }
              if (msg.includes('date') && msg.includes('valid')) {
                return '有効な日付を入力してください'
              }
              if (msg.includes('date') && msg.includes('future')) {
                return '未来の日付は選択できません'
              }

              // 時刻関連
              if (msg.includes('check_in') && msg.includes('required')) {
                return '出勤時刻は必須項目です'
              }
              if (msg.includes('check_in') && msg.includes('format')) {
                return '出勤時刻はHH:MM形式で入力してください'
              }
              if (msg.includes('check_out') && msg.includes('after')) {
                return '退勤時刻は出勤時刻より後の時刻を入力してください'
              }
              if (msg.includes('check_out') && msg.includes('format')) {
                return '退勤時刻はHH:MM形式で入力してください'
              }

              // 休憩時間関連
              if (msg.includes('rest_start') && msg.includes('required')) {
                return '休憩開始時刻は必須項目です'
              }
              if (msg.includes('rest_end') && msg.includes('required')) {
                return '休憩終了時刻は必須項目です'
              }
              if (msg.includes('rest_end') && msg.includes('after')) {
                return '休憩終了時刻は休憩開始時刻より後の時刻を入力してください'
              }

              // 理由関連
              if (msg.includes('reason') && msg.includes('required')) {
                return '申請理由は必須項目です'
              }
              if (msg.includes('reason') && msg.includes('max')) {
                return '申請理由は500文字以内で入力してください'
              }

              // その他
              if (
                msg.includes('already exists') ||
                msg.includes('pending request')
              ) {
                return 'この日付は既に申請中です'
              }

              return msg
            })

            this.errors[field] = translatedMessages
          }
        }

        let message = '申請の提出に失敗しました'
        if (error.response?.data?.message) {
          const apiMessage = error.response.data.message
          if (
            apiMessage.includes('already exists') ||
            apiMessage.includes('pending request')
          ) {
            message = 'この日付は既に申請中です'
          } else if (apiMessage.includes('not found')) {
            message = '対象の勤怠データが見つかりません'
          } else {
            message = apiMessage
          }
        }

        if (this.$toast) {
          this.$toast.error(message)
        }
      } finally {
        this.isSubmitting = false
      }
    },

    addRest () {
      this.form.rests.push({
        rest_start: '',
        rest_end: ''
      })
    },

    removeRest (index) {
      this.form.rests.splice(index, 1)
    },

    clearForm () {
      // 時間関連のフィールドをクリア
      this.form.check_in = ''
      this.form.check_out = ''
      this.form.rests = []
    },

    clearAllForm () {
      // フォームを完全にリセット
    },

    resetFormCompletely () {
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

    validateForm () {
      const errors = {}

      // 対象日のチェック
      if (!this.form.date) {
        errors.date = ['対象日は必須項目です']
      }

      // 出勤時刻のチェック
      if (!this.form.check_in) {
        errors.check_in = ['出勤時刻は必須項目です']
      }

      // 退勤時刻のチェック（退勤時刻が入力されている場合）
      if (this.form.check_out && this.form.check_in) {
        if (this.form.check_out <= this.form.check_in) {
          errors.check_out = [
            '退勤時刻は出勤時刻より後の時刻を入力してください'
          ]
        }
      }

      // 申請理由のチェック
      if (!this.form.reason || this.form.reason.trim() === '') {
        errors.reason = ['申請理由は必須項目です']
      } else if (this.form.reason.length > 500) {
        errors.reason = ['申請理由は500文字以内で入力してください']
      }

      // 休憩時間のチェック
      this.form.rests.forEach((rest, index) => {
        // 開始時刻と終了時刻が両方入力されているか、両方空であること
        const hasStart = rest.rest_start && rest.rest_start.trim() !== ''
        const hasEnd = rest.rest_end && rest.rest_end.trim() !== ''

        if (hasStart && !hasEnd) {
          errors[`rests.${index}.rest_end`] = ['休憩終了時刻は必須項目です']
        } else if (!hasStart && hasEnd) {
          errors[`rests.${index}.rest_start`] = ['休憩開始時刻は必須項目です']
        } else if (hasStart && hasEnd) {
          // 両方入力されている場合は時刻の前後関係をチェック
          if (rest.rest_end <= rest.rest_start) {
            errors[`rests.${index}.rest_end`] = [
              '休憩終了時刻は休憩開始時刻より後の時刻を入力してください'
            ]
          }

          // 休憩時間が勤務時間内に収まっているかチェック
          if (this.form.check_in && rest.rest_start < this.form.check_in) {
            errors[`rests.${index}.rest_start`] = [
              '休憩開始時刻は出勤時刻より後の時刻を入力してください'
            ]
          }

          if (this.form.check_out && rest.rest_start > this.form.check_out) {
            errors[`rests.${index}.rest_start`] = [
              '休憩開始時刻は退勤時刻より前の時刻を入力してください'
            ]
          }

          if (this.form.check_out && rest.rest_end > this.form.check_out) {
            errors[`rests.${index}.rest_end`] = [
              '休憩終了時刻は退勤時刻より前の時刻を入力してください'
            ]
          }
        }
      })

      return errors
    },

    getErrorMessage (error) {
      // エラーが配列の場合は最初のメッセージを取得
      if (Array.isArray(error)) {
        return error.length > 0 ? this.translateErrorMessage(error[0]) : ''
      }
      // エラーが文字列の場合はそのまま翻訳
      return this.translateErrorMessage(error)
    },

    translateErrorMessage (msg) {
      if (!msg) { return '' }

      // 既に日本語の場合はそのまま返す
      if (
        /[\u3000-\u303F\u3040-\u309F\u30A0-\u30FF\uFF00-\uFF9F\u4E00-\u9FAF\u3400-\u4DBF]/.test(
          msg
        )
      ) {
        return msg
      }

      // 英語メッセージを日本語に変換
      const lowerMsg = msg.toLowerCase()

      // check_in (出勤時刻)
      if (lowerMsg.includes('check in') && lowerMsg.includes('required')) {
        return '出勤時刻は必須項目です'
      }
      if (lowerMsg.includes('check in') && lowerMsg.includes('format')) {
        return '出勤時刻はHH:MM形式で入力してください'
      }

      // check_out (退勤時刻)
      if (lowerMsg.includes('check out') && lowerMsg.includes('required')) {
        return '退勤時刻は必須項目です'
      }
      if (
        lowerMsg.includes('check out') &&
        (lowerMsg.includes('after') || lowerMsg.includes('greater'))
      ) {
        return '退勤時刻は出勤時刻より後の時刻を入力してください'
      }
      if (lowerMsg.includes('check out') && lowerMsg.includes('format')) {
        return '退勤時刻はHH:MM形式で入力してください'
      }

      // date (日付)
      if (lowerMsg.includes('date') && lowerMsg.includes('required')) {
        return '対象日は必須項目です'
      }
      if (
        lowerMsg.includes('date') &&
        (lowerMsg.includes('valid') || lowerMsg.includes('format'))
      ) {
        return '有効な日付を入力してください'
      }
      if (
        lowerMsg.includes('date') &&
        (lowerMsg.includes('future') || lowerMsg.includes('before'))
      ) {
        return '未来の日付は選択できません'
      }

      // reason (理由)
      if (lowerMsg.includes('reason') && lowerMsg.includes('required')) {
        return '申請理由は必須項目です'
      }
      if (lowerMsg.includes('reason') && lowerMsg.includes('max')) {
        return '申請理由は500文字以内で入力してください'
      }

      // rest (休憩)
      if (
        lowerMsg.includes('rest') &&
        lowerMsg.includes('start') &&
        lowerMsg.includes('required')
      ) {
        return '休憩開始時刻は必須項目です'
      }
      if (
        lowerMsg.includes('rest') &&
        lowerMsg.includes('end') &&
        lowerMsg.includes('required')
      ) {
        return '休憩終了時刻は必須項目です'
      }
      if (
        lowerMsg.includes('rest') &&
        lowerMsg.includes('end') &&
        (lowerMsg.includes('after') || lowerMsg.includes('greater'))
      ) {
        return '休憩終了時刻は休憩開始時刻より後の時刻を入力してください'
      }
      if (
        lowerMsg.includes('rest') &&
        lowerMsg.includes('start') &&
        lowerMsg.includes('check in')
      ) {
        return '休憩開始時刻は出勤時刻より後の時刻を入力してください'
      }
      if (
        lowerMsg.includes('rest') &&
        lowerMsg.includes('start') &&
        lowerMsg.includes('check out')
      ) {
        return '休憩開始時刻は退勤時刻より前の時刻を入力してください'
      }
      if (
        lowerMsg.includes('rest') &&
        lowerMsg.includes('end') &&
        lowerMsg.includes('check out')
      ) {
        return '休憩終了時刻は退勤時刻より前の時刻を入力してください'
      }

      // その他
      if (lowerMsg.includes('already') || lowerMsg.includes('pending')) {
        return 'この日付は既に申請中です'
      }

      // 翻訳できない場合は元のメッセージを返す
      return msg
    },

    formatTime (timeString) {
      if (!timeString) { return '' }
      // HH:MM:SS形式をHH:MM形式に変換
      return timeString.substring(0, 5)
    },

    formatDate (dateInput) {
      if (!dateInput) { return '' }

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
    },

    formatDateTime (dateTimeString) {
      if (!dateTimeString) { return '−' }
      const date = new Date(dateTimeString)
      if (isNaN(date.getTime())) { return '−' }

      return date.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    calculateWorkHours (attendance) {
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
        const totalBreakMilliseconds = attendance.rests.reduce(
          (total, rest) => {
            if (rest.rest_start && rest.rest_end) {
              const restStart = new Date(`${dateStr}T${rest.rest_start}`)
              const restEnd = new Date(`${dateStr}T${rest.rest_end}`)

              if (!isNaN(restStart.getTime()) && !isNaN(restEnd.getTime())) {
                return total + (restEnd - restStart)
              }
            }
            return total
          },
          0
        )

        workMilliseconds -= totalBreakMilliseconds
      }

      if (workMilliseconds <= 0) { return '0:00' }

      const hours = Math.floor(workMilliseconds / (1000 * 60 * 60))
      const minutes = Math.floor(
        (workMilliseconds % (1000 * 60 * 60)) / (1000 * 60)
      )

      return `${hours}:${String(minutes).padStart(2, '0')}`
    },

    // イベントハンドラーメソッド（必要に応じて処理を追加）
    onDateFocus () {},
    onDateBlur () {},
    onTimeFocus (field) {},
    onTimeBlur (field) {},
    onRestFocus (field, index) {},
    onRestBlur (field, index) {}
  }
}
</script>

<style src="@/assets/css/pages/correction-requests-new.css" scoped></style>
