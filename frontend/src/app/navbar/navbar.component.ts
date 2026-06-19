import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { CategorieService } from 'src/services/categories.service';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})
export class NavbarComponent implements OnInit {

  categories: any[] = [];
  activeCatId: number | null = null;
  activeType: string | null = null;

  // Catégories par défaut (affichées immédiatement même si l'API est offline)
  private defaultCategories: any[] = [
    { id: 1, nom: 'Animaux',    icone: 'fa-paw',                enfants: [] },
    { id: 2, nom: 'Emploi',     icone: 'fa-briefcase',          enfants: [] },
    {
      id: 3, nom: 'High-Tech',  icone: 'fa-laptop',
      enfants: [
        { id: 31, nom: 'Informatique' },
        { id: 32, nom: 'Téléphones' },
        { id: 33, nom: 'Audio / Vidéo' },
      ]
    },
    { id: 4, nom: 'Immobilier', icone: 'fa-house',              enfants: [] },
    { id: 5, nom: 'Loisirs',    icone: 'fa-gamepad',            enfants: [] },
    { id: 6, nom: 'Maison',     icone: 'fa-couch',              enfants: [] },
    { id: 7, nom: 'Mode',       icone: 'fa-shirt',              enfants: [] },
    { id: 8, nom: 'Services',   icone: 'fa-screwdriver-wrench', enfants: [] },
    { id: 9, nom: 'Véhicules',  icone: 'fa-car',                enfants: [] },
  ];

  constructor(
    private categorieService: CategorieService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    // Affichage IMMÉDIAT des catégories par défaut
    this.categories = this.defaultCategories;

    // Tentative de chargement depuis l'API en arrière-plan
    this.categorieService.getAll().subscribe({
      next: (res) => {
        if (res.success && res.data && res.data.length > 0) {
          // Si l'API retourne des données, on les utilise
          this.categories = res.data.map((c: any) => ({
            ...c,
            enfants: c.enfants || []
          }));
        }
        // Sinon on garde les catégories par défaut
      },
      error: () => {
        // En cas d'erreur, on garde les catégories par défaut
      }
    });

    this.route.queryParams.subscribe(params => {
      this.activeCatId = params['cat_id'] ? +params['cat_id'] : null;
      this.activeType  = params['type'] || null;
    });
  }

  isAllActive(): boolean {
    return !this.activeCatId && !this.activeType;
  }

  isDonActive(): boolean {
    return this.activeType === 'don';
  }

  isCatActive(cat: any): boolean {
    if (this.activeCatId === cat.id) return true;
    if (cat.enfants && cat.enfants.length > 0) {
      return cat.enfants.some((e: any) => e.id === this.activeCatId);
    }
    return false;
  }

  isChildActive(enfantId: number): boolean {
    return this.activeCatId === enfantId;
  }
}
