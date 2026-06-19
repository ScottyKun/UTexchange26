export class User {
  id: number = 0;
  nom: string = '';
  prenom: string = '';
  email: string = '';
  campus: string = '';
  est_actif: boolean = true;
  email_verifie: boolean = false;
  role_id: number = 2;
  date_inscription: string = '';
  modif_inscription: string = '';

  constructor(data: Partial<User> = {}) {
    Object.assign(this, data);
  }

  get fullName(): string {
    return `${this.prenom} ${this.nom}`.trim();
  }

  get isAdmin(): boolean {
    return this.role_id === 5;
  }
}