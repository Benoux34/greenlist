import { http } from "@/shared/api/http";
import type { RegisterInput, User } from "./types";

const fetchMe = () => http.get<User>("/me");

const login = (email: string, password: string) =>
  http.post<void>("/auth/login", { email, password });

const register = (input: RegisterInput) =>
  http.post<User>("/auth/register", input);

const logout = () => http.post<void>("/auth/logout");

const requestPasswordReset = (email: string) =>
  http.post<void>("/auth/password/forgot", { email });

const checkResetCode = (email: string, code: string) =>
  http.post<void>("/auth/password/check", { email, code });

const resetPassword = (email: string, code: string, password: string) =>
  http.post<void>("/auth/password/reset", { email, code, password });

export {
  fetchMe,
  login,
  register,
  logout,
  requestPasswordReset,
  checkResetCode,
  resetPassword,
};
