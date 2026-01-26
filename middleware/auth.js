export default async function ({ store, redirect, route }) {
  // 認証状態の復元(完了を待つ)
  if (process.client && !store.getters["auth/isAuthenticated"]) {
    await store.dispatch("auth/restoreAuth");
  }

  // 認証されていない場合はログインページにリダイレクト
  if (!store.getters["auth/isAuthenticated"]) {
    return redirect("/login?redirect=" + encodeURIComponent(route.fullPath));
  }
}
