type Friend = Readonly<{
  id: string;
  firstName: string;
  since: string;
}>;

type InvitationLink = Readonly<{
  token: string;
  expiresAt: string;
}>;

type Invitation = Readonly<{
  inviter: Readonly<{ id: string; firstName: string }>;
  expiresAt: string;
}>;

export type { Friend, Invitation, InvitationLink };
