import { Component, OnInit } from '@angular/core';
import { CategorieService } from 'src/services/categories.service';
import { Categorie } from 'src/models/categorie';

@Component({ selector: 'app-categories-list', templateUrl: './categories-list.component.html' })
export class CategoriesListComponent implements OnInit {
  categories: Categorie[] = [];
  loading = true;
  showForm = false;
  newNom = '';
  newParentId: number | null = null;

  constructor(private categorieService: CategorieService) {}

  ngOnInit(): void {
    this.load();
  }

  load(): void {
    this.loading = true;
    this.categorieService.getAll().subscribe({
      next: (res) => { if (res.success) this.categories = res.data; this.loading = false; },
      error: () => this.loading = false
    });
  }

  onCreate(): void {
    if (!this.newNom.trim()) return;
    this.categorieService.create({ nom: this.newNom, parent_id: this.newParentId }).subscribe(res => {
      if (res.success) { this.newNom = ''; this.newParentId = null; this.showForm = false; this.load(); }
    });
  }

  toggle(cat: Categorie): void {
    const action = cat.is_active ? this.categorieService.deactivate(cat.id) : this.categorieService.activate(cat.id);
    action.subscribe(res => { if (res.success) cat.is_active = !cat.is_active; });
  }

  delete(id: number): void {
    if (!confirm('Supprimer cette catégorie ?')) return;
    this.categorieService.delete(id).subscribe(res => {
      if (res.success) this.categories = this.categories.filter(c => c.id !== id);
    });
  }

  getParentNom(parentId: number | null): string {
    if (!parentId) return '—';
    return this.categories.find(c => c.id === parentId)?.nom || '—';
  }
}
