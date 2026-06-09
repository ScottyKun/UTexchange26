import { User } from './user';
import { Photo } from './photo';

export type AnnonceType   = 'vente' | 'location' | 'don' | 'troc';
export type AnnonceStatus = 'active' | 'vendu' | 'draft' | 'signale' | 'expire';

export class Annonce {
  id: number = 0;
  user_id: number = 0;
  categorie_id: number = 0;
  title: string = '';
  description: string = '';
  price: number = 0;
  type: AnnonceType = 'vente';
  status: AnnonceStatus = 'active';
  location: string = '';
  view_count: number = 0;
  created_at: string = '';
  seller?: User;
  photos: Photo[] = [];
  avis_stats?: { total: number; moyenne: number };

  constructor(data: Partial<Annonce> = {}) {
    Object.assign(this, data);
    if (data.seller) this.seller = new User(data.seller);
    if (data.photos) this.photos = data.photos.map(p => new Photo(p));
  }

  get coverPhoto(): Photo | undefined {
    return this.photos.find(p => p.is_cover) ?? this.photos[0];
  }

  get isActive(): boolean {
    return this.status === 'active';
  }

  get isFree(): boolean {
    return this.type === 'don';
  }

  get isTroc(): boolean{
    return this.type === 'troc';
  }
}

export interface AnnonceFilters {
  cat_id?: number;
  search?: string;
  min_price?: number;
  max_price?: number;
  type?: AnnonceType;
}
 
export interface AnnonceFormData {
  categorie_id: number;
  title: string;
  description: string;
  price: number;
  type: AnnonceType;
  status?: AnnonceStatus;
  location?: string;
}