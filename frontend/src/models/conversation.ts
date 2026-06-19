import { Message } from './message';
import { Avis } from './avis';

export type ConversationStatus = 'active' | 'terminee';

export class Conversation {
  id: string = '';           // MongoDB ObjectId
  annonce_id: number = 0;
  annonce_title: string = '';
  acheteur_id: number = 0;
  acheteur_nom: string = '';
  vendeur_id: number = 0;
  vendeur_nom: string = '';
  status: ConversationStatus = 'active';
  last_message: string | null = null;
  last_message_at: string | null = null;
  created_at: string = '';
  messages: Message[] = [];
  avis: Avis[] = [];

  constructor(data: Partial<Conversation> = {}) {
    Object.assign(this, data);
    if (data.messages) this.messages = data.messages.map(m => new Message(m));
    if (data.avis)     this.avis     = data.avis.map(a => new Avis(a));
  }

  get isActive(): boolean {
    return this.status === 'active';
  }

  get isTerminee(): boolean {
    return this.status === 'terminee';
  }

  isParticipant(userId: number): boolean {
    return this.acheteur_id === userId || this.vendeur_id === userId;
  }

  hasAvisFromUser(userId: number): boolean {
    return this.avis.some(a => a.acheteur_id === userId);
  }

  unreadCount(userId: number): number {
    return this.messages.filter(m => !m.is_read && m.expediteur_id !== userId).length;
  }
}