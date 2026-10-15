type User = Readonly<{
  id: string;
  email: string;
  firstName: string;
  createdAt: string;
}>;

type RegisterInput = Readonly<{
  firstName: string;
  email: string;
  password: string;
}>;

export type { User, RegisterInput };
