export const state = () => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: false,
});

export const mutations = {
  SET_USER(state, user) {
    state.user = user;
    state.isAuthenticated = !!user;
  },

  SET_TOKEN(state, token) {
    state.token = token;
  },

  SET_LOADING(state, loading) {
    state.isLoading = loading;
  },

  LOGOUT(state) {
    state.user = null;
    state.token = null;
    state.isAuthenticated = false;
  },
};

export const getters = {
  isAuthenticated: (state) => state.isAuthenticated,
  isLoading: (state) => state.isLoading,
  user: (state) => state.user,
  token: (state) => state.token,
  isEmailVerified: (state) => state.user?.email_verified_at !== null,
  isAdmin: (state) => state.user?.role === "admin",
};

export const actions = {
  // API ログイン
  async login({ commit }, credentials) {
    try {
      commit("SET_LOADING", true);

      const response = await this.$axios.$post("/api/login", {
        email: credentials.email,
        password: credentials.password,
      });

      // 認証情報をlocalStorageに保存
      localStorage.setItem("auth-token", response.token);
      localStorage.setItem("auth-user", JSON.stringify(response.user));

      // Axiosのデフォルトヘッダーにトークンを設定
      this.$axios.setToken(response.token, "Bearer");

      commit("SET_USER", response.user);
      commit("SET_TOKEN", response.token);

      return { success: true, user: response.user };
    } catch (error) {
      console.error("Login error:", error);
      console.error("Error response:", error.response);

      // エラーメッセージを日本語化
      let message = "ログインに失敗しました";
      if (error.response?.data?.message) {
        const apiMessage = error.response.data.message;
        if (apiMessage === "Invalid credentials") {
          message = "メールアドレスまたはパスワードが正しくありません";
        } else {
          message = apiMessage;
        }
      }

      return { success: false, error: message };
    } finally {
      commit("SET_LOADING", false);
    }
  },

  // API 登録
  async register({ commit }, userData) {
    try {
      commit("SET_LOADING", true);

      const response = await this.$axios.$post("/api/register", {
        name: userData.name,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.password_confirmation,
      });

      // 登録と同時にログイン（メール認証は未完了）
      localStorage.setItem("auth-token", response.token);
      localStorage.setItem("auth-user", JSON.stringify(response.user));

      this.$axios.setToken(response.token, "Bearer");

      commit("SET_USER", response.user);
      commit("SET_TOKEN", response.token);

      return {
        success: true,
        message: response.message,
        shouldRedirectToVerify: true, // メール認証ページへのリダイレクトフラグ
      };
    } catch (error) {
      let message = "登録に失敗しました";

      // ネットワークエラーの特別な処理
      if (error.code === "ECONNABORTED") {
        message = "リクエストがタイムアウトしました。もう一度お試しください。";
      } else if (!error.response) {
        message =
          "ネットワークエラーが発生しました。サーバーに接続できません。";
      }

      // バリデーションエラーがある場合は詳細を表示
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        const errorMessages = [];

        // エラーメッセージを日本語化
        for (const [field, messages] of Object.entries(errors)) {
          messages.forEach((msg) => {
            if (msg.includes("email") && msg.includes("already been taken")) {
              errorMessages.push("このメールアドレスは既に登録されています");
            } else if (
              msg.includes("password") &&
              msg.includes("confirmation does not match")
            ) {
              errorMessages.push("パスワードが一致しません");
            } else if (msg.includes("required")) {
              const fieldName =
                field === "name"
                  ? "名前"
                  : field === "email"
                  ? "メールアドレス"
                  : field === "password"
                  ? "パスワード"
                  : field;
              errorMessages.push(`${fieldName}は必須項目です`);
            } else {
              errorMessages.push(msg);
            }
          });
        }

        message = errorMessages.join(", ");
      } else if (error.response?.data?.message) {
        message = error.response.data.message;
      }

      return { success: false, error: message };
    } finally {
      commit("SET_LOADING", false);
    }
  },

  // API ログアウト
  async logout({ commit, dispatch }) {
    try {
      const token = localStorage.getItem("auth-token");
      if (token) {
        this.$axios.setToken(token, "Bearer");
        await this.$axios.$post("/api/logout");
      }
    } catch (error) {
      // ログアウトAPIが失敗してもローカルの認証情報はクリア
      console.warn("API logout failed:", error);
    } finally {
      localStorage.removeItem("auth-token");
      localStorage.removeItem("auth-user");
      this.$axios.setToken(false);

      commit("LOGOUT");

      // 勤怠ストアもリセット
      dispatch("attendance/resetAttendance", null, { root: true });

      return { success: true };
    }
  },

  // 認証状態の復元(ページリロード時)
  async restoreAuth({ commit, dispatch }) {
    try {
      const token = localStorage.getItem("auth-token");
      const user = localStorage.getItem("auth-user");

      if (token && user) {
        this.$axios.setToken(token, "Bearer");
        commit("SET_TOKEN", token);
        commit("SET_USER", JSON.parse(user));

        // サーバーから最新のユーザー情報を取得
        try {
          const updatedUser = await dispatch("fetchUser");
          if (updatedUser) {
            localStorage.setItem("auth-user", JSON.stringify(updatedUser));
          }
        } catch (error) {
          // サーバーから取得できない場合はキャッシュを使用
        }
      }
    } catch (error) {
      localStorage.removeItem("auth-token");
      localStorage.removeItem("auth-user");
      this.$axios.setToken(false);
    }
  },

  // ユーザー情報を更新
  async fetchUser({ commit }) {
    try {
      const response = await this.$axios.$get("/api/user");

      // レスポンスが { user: {...} } または直接ユーザーオブジェクトの場合に対応
      const user = response?.user || response;

      if (user && user.id) {
        commit("SET_USER", user);
        return user;
      } else {
        console.error("fetchUser - No valid user in response");
        return null;
      }
    } catch (error) {
      // 認証エラーの場合はログアウト
      if (error.response?.status === 401) {
        commit("LOGOUT");
        localStorage.removeItem("auth-token");
        localStorage.removeItem("auth-user");
        this.$axios.setToken(false);
      }
      throw error;
    }
  },
};
