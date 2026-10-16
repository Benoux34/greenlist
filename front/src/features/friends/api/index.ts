import { http } from "@/shared/api/http";
import type { Friend, Invitation, InvitationLink } from "../entities";

const fetchFriends = () => http.get<Friend[]>("/friends");

const removeFriend = (id: string) => http.delete(`/friends/${id}`);

const createInvitation = () =>
  http.post<InvitationLink>("/friends/invitations");

const fetchInvitation = (token: string) =>
  http.get<Invitation>(`/friends/invitations/${token}`);

const acceptInvitation = (token: string) =>
  http.post<void>(`/friends/invitations/${token}/accept`);

const invitationUrl = (token: string) =>
  `${window.location.origin}/invitation/${token}`;

export {
  fetchFriends,
  removeFriend,
  createInvitation,
  fetchInvitation,
  acceptInvitation,
  invitationUrl,
};
