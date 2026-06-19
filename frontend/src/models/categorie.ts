export class Categorie {
  id: number = 0;
  nom: string = '';
  parent_id: number | null = null;
  is_active: boolean = true;

  constructor(data: Partial<Categorie> = {}) {
    Object.assign(this, data);
  }
}