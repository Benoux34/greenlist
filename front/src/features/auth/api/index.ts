import { http } from "@/shared/api/http";
import type { RegisterInput, User } from "../entities";

const fetchMe = () => http.get<User>("/me");

const login = (email: string, password: string) =>
  http.post<void>("/auth/login", { email, password });

const register = (input: RegisterInput) =>
  http.post<User>("/auth/register", input);

const logout = () => http.post<void>("/auth/logout");

export { fetchMe, login, register, logout };
