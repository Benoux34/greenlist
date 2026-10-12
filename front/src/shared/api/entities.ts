type ApiProblem = Readonly<{
  status: number;
  title?: string;
  detail?: string;
  violations?: { propertyPath: string; title: string }[];
}>;

type Paginated<T> = Readonly<{
  items: T[];
  total: number;
  page: number;
  perPage: number;
}>;

export type { ApiProblem, Paginated };
