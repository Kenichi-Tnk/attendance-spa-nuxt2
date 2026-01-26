module.exports = {
  root: true,
  env: {
    browser: true,
    node: true
  },
  parserOptions: {
    parser: '@babel/eslint-parser',
    requireConfigFile: false
  },
  extends: ['@nuxtjs'],
  plugins: [],
  // カスタムルール
  rules: {
    // console.logを本番環境でエラー、開発環境で警告にする
    'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'warn',

    // 使われていない変数を警告
    'no-unused-vars': [
      'warn',
      {
        vars: 'all',
        args: 'after-used',
        ignoreRestSiblings: false
      }
    ],

    // デバッガーを本番環境でエラーにする
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'warn',

    // Vue関連の厳しすぎるルールを緩和
    'vue/multi-word-component-names': 'off',
    'vue/no-v-html': 'off'
  }
}
