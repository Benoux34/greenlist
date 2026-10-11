type ApiProblem = Readonly<{
  status: number;
  title?: string;
  detail?: string;
  violations?: { propertyPath: string; title: string }[];
}>;

export type { ApiProblem };
