export class Photo {
  id: number = 0;
  annonce_id: number = 0;
  chemin_fichier: string = '';
  nom_fichier: string = '';
  taille_fichier: number = 0;
  is_cover: boolean = false;

  constructor(data: Partial<Photo> = {}) {
    Object.assign(this, data);
  }
}