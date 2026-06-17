export class Avis {
  id: string = '';           
  acheteur_id: number = 0;
  vendeur_id: number = 0;
  note: number = 0;        
  commentaire: string = '';
  is_active: boolean = true;
  created_at: string = '';
  conversation_id: number = 0;

  // Champs enrichis (agrégation)
  annonce_title?: string;
  acheteur_nom?: string;
  vendeur_nom?: string;

  constructor(data: Partial<Avis> = {}) {
    Object.assign(this, data);
  }

  get stars(): number[] {
    return Array(this.note).fill(0);
  }
}

export class AvisStats {
  total: number = 0;
  moyenne: number = 0;

  constructor(data: Partial<AvisStats> = {}) {
    Object.assign(this, data);
  }

  get moyenneArrondie(): string {
    return this.moyenne.toFixed(1);
  }
}

export interface AvisStats {
  total: number;
  moyenne: number;
}
 
export interface AvisFormData {
  note: number;
  commentaire: string;
}