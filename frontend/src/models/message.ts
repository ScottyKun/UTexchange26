export class Message {
  id: string = '';        
  expediteur_id: number = 0;
  contenu: string = '';
  is_read: boolean = false;
  created_at: string = '';

  constructor(data: Partial<Message> = {}) {
    Object.assign(this, data);
  }

  isFrom(userId: number): boolean {
    return this.expediteur_id === userId;
  }
}