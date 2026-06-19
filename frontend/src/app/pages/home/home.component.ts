import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/services/auth.service';
import { AnnonceService } from 'src/services/annonces.service';
import { Annonce } from 'src/models/annonce';

interface CategorySection {
  id: number;
  nom: string;
  icone: string;
  annonces: Annonce[];
}

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
})
export class HomeComponent implements OnInit {
  annoncesByCategory: CategorySection[] = [];
  loading = true;

  constructor(public auth: AuthService, private annonceService: AnnonceService) {}

  ngOnInit(): void {
    // Charger les annonces depuis l'API
    this.annonceService.getAll().subscribe({
      next: (res) => {
        if (res.success && res.data) {
          this.groupByCategory(res.data);
        }
        this.loading = false;
      },
      error: () => {
  this.loading = false;
  this.annoncesByCategory = [
    {
      id: 3, nom: 'High-Tech', icone: 'fa-laptop',
      annonces: [
        new Annonce({ id: 1, title: 'Iphone 15', price: 500, type: 'vente', location: 'Belfort', status: 'active', description: '', user_id: 0, categorie_id: 3, view_count: 0, created_at: '', photos: [] }),
        new Annonce({ id: 2, title: 'Lenovo Legion', price: 1799.99, type: 'vente', location: 'Sevenans', status: 'active', description: '', user_id: 0, categorie_id: 3, view_count: 0, created_at: '', photos: [] }),
      ]
    },
    {
      id: 4, nom: 'Immobilier', icone: 'fa-house',
      annonces: [
        new Annonce({ id: 3, title: 'Studio meublé 18m²', price: 0, type: 'don', location: 'Belfort', status: 'active', description: '', user_id: 0, categorie_id: 4, view_count: 0, created_at: '', photos: [] }),
      ]
    },
  ];
}
    });
  }

  private groupByCategory(annonces: Annonce[]): void {
    const map = new Map<number, CategorySection>();
    annonces.forEach(a => {
      if (!map.has(a.categorie_id)) {
        map.set(a.categorie_id, { id: a.categorie_id, nom: 'Catégorie', icone: 'fa-tag', annonces: [] });
      }
      map.get(a.categorie_id)!.annonces.push(a);
    });
    this.annoncesByCategory = Array.from(map.values());
  }

  getPhotoUrl(annonce: Annonce): string | null {
    const cover = annonce.coverPhoto;
    if (!cover) return null;
    return `http://localhost:8000/api/photos/${annonce.id}/${cover.nom_fichier}`;
  }

  formatPrice(annonce: Annonce): string {
    if (annonce.isFree) return 'Gratuit';
    if (annonce.isTroc) return 'Troc';
    return annonce.price.toFixed(2).replace('.', ',') + ' €';
  }

  priceClass(annonce: Annonce): string {
    if (annonce.isFree) return 'annonce-card-h__price annonce-card-h__price--free';
    if (annonce.isTroc) return 'annonce-card-h__price annonce-card-h__price--troc';
    return 'annonce-card-h__price';
  }
}
