import type { ZodError } from "zod";

export type FieldErrors<T> = Partial<Record<keyof T, string>>;

function fieldErrors<T>(error: ZodError<T>): FieldErrors<T> {
  const errors: FieldErrors<T> = {};

  for (const issue of error.issues) {
    const field = issue.path[0] as keyof T;
    errors[field] ??= issue.message;
  }

  return errors;
}

export { fieldErrors };
