import { extend, ValidationObserver, ValidationProvider, localize } from 'vee-validate'
import { required, email, min, max, confirmed, regex } from 'vee-validate/dist/rules'
import Vue from 'vue'

// コンポーネントを登録
Vue.component('ValidationObserver', ValidationObserver)
Vue.component('ValidationProvider', ValidationProvider)

// ルールを登録
extend('required', required)
extend('email', email)
extend('min', min)
extend('max', max)
extend('confirmed', confirmed)
extend('regex', regex)

// カスタムルール: time形式（HH:MM）
extend('time', {
  validate: (value) => {
    return /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value)
  },
  message: '時刻はHH:MM形式で入力してください'
})

// カスタムルール: date形式（YYYY-MM-DD）
extend('date', {
  validate: (value) => {
    return /^\d{4}-\d{2}-\d{2}$/.test(value) && !isNaN(new Date(value).getTime())
  },
  message: '日付はYYYY-MM-DD形式で入力してください'
})

// カスタムルール: 未来の日付は不可
extend('not_future', {
  validate: (value) => {
    const inputDate = new Date(value)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return inputDate <= today
  },
  message: '未来の日付は選択できません'
})

// カスタムルール: 時刻の前後関係チェック
extend('after_time', {
  params: ['target'],
  validate: (value, { target }) => {
    if (!value || !target) return true
    const [valueHour, valueMin] = value.split(':').map(Number)
    const [targetHour, targetMin] = target.split(':').map(Number)
    const valueMinutes = valueHour * 60 + valueMin
    const targetMinutes = targetHour * 60 + targetMin
    return valueMinutes > targetMinutes
  },
  message: (fieldName, { target }) => `${fieldName}は開始時刻より後の時刻を入力してください`
})

// 日本語メッセージ
localize('ja', {
  messages: {
    required: '{_field_}は必須項目です',
    email: '有効なメールアドレスを入力してください',
    min: '{_field_}は{length}文字以上で入力してください',
    max: '{_field_}は{length}文字以内で入力してください',
    confirmed: '{_field_}が一致しません',
    regex: '{_field_}の形式が正しくありません'
  },
  names: {
    name: '名前',
    email: 'メールアドレス',
    password: 'パスワード',
    password_confirmation: 'パスワード（確認）',
    reason: '理由',
    date: '日付',
    start_time: '開始時刻',
    end_time: '終了時刻',
    corrected_start_time: '修正後の開始時刻',
    corrected_end_time: '修正後の終了時刻',
    corrected_break_time: '修正後の休憩時間'
  }
})
