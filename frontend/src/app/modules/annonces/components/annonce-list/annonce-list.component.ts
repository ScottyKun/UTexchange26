import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AnnonceService } from 'src/services/annonces.service';
import { CategorieService } from 'src/services/categories.service';
import { FavoriService } from 'src/services/favoris.service';
import { AuthService } from 'src/services/auth.service';
import { Annonce, AnnonceFilters, AnnonceType } from 'src/models/annonce';
import { Categorie } from 'src/models/categorie';

@Component({
  selector: 'app-annonce-list',
  templateUrl: './annonce-list.component.html',
  styleUrls: ['./annonce-list.component.css']
})
export class AnnonceListComponent implements OnInit {

  annonces:   Annonce[]   = [];
  categories: Categorie[] = [];
  favoriIds:  Set<number> = new Set();
  loading = true;
  errorMsg = '';

  filters: AnnonceFilters = {};
  searchInput = '';

  readonly types: { value: AnnonceType | ''; label: string }[] = [
    { value: '',          label: 'Tous les types' },
    { value: 'vente',     label: 'Vente' },
    { value: 'location',  label: 'Location' },
    { value: 'don',       label: 'Don' },
    { value: 'troc',      label: 'Troc' }
  ];

  constructor(
    private annonceService: AnnonceService,
    private categorieService: CategorieService,
    private favoriService: FavoriService,
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.categorieService.getAll().subscribe(res => {
      this.categories = res.data ?? [];
    });

    // Sync filtres depuis query params
    this.route.queryParams.subscribe(params => {
      this.filters = {
        cat_id:    params['cat_id']    ? +params['cat_id']  : undefined,
        search:    params['search']    ?? undefined,
        type:      params['type']      ?? undefined,
        min_price: params['min_price'] ? +params['min_price'] : undefined,
        max_price: params['max_price'] ? +params['max_price'] : undefined
      };
      this.searchInput = this.filters.search ?? '';
      this.loadAnnonces();
    });

    if (this.authService.isAuthenticated()) { this.loadFavoris(); }
  }

  loadAnnonces(): void {
    this.loading = true;
    this.annonceService.getAll(this.filters).subscribe({
      next: res => { this.annonces = res.data ?? []; this.loading = false; },
      error: () => { this.errorMsg = 'Impossible de charger les annonces'; this.loading = false; }
    });
  }

  loadFavoris(): void {
    this.favoriService.getAll().subscribe(res => {
      this.favoriIds = new Set((res.data ?? []).map(a => a.id));
    });
  }

  applySearch(): void {
    this.updateQueryParams({ search: this.searchInput || null });
  }

  applyFilter(key: keyof AnnonceFilters, value: any): void {
    this.updateQueryParams({ [key]: value || null });
  }

  updateQueryParams(params: any): void {
    this.router.navigate([], { relativeTo: this.route, queryParams: params, queryParamsHandling: 'merge' });
  }

  onToggleFavori(id: number): void {
    if (!this.authService.isAuthenticated()) { this.router.navigate(['/login']); return; }
    this.favoriService.toggle(id).subscribe(res => {
      if (res.data?.action === 'added') { this.favoriIds.add(id); }
      else { this.favoriIds.delete(id); }
    });
  }

  isFavori(id: number): boolean { return this.favoriIds.has(id); }
}

