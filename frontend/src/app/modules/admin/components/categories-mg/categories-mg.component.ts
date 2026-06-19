import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Categorie } from 'src/models/categorie';
import { CategorieService } from 'src/services/categories.service';

type FormMode = 'none' | 'add' | 'edit';

@Component({
  selector: 'app-categories-mg',
  templateUrl: './categories-mg.component.html',
  styleUrls: ['./categories-mg.component.css']
})
export class CategoriesMgComponent implements OnInit {

  categories: Categorie[]  = [];
  parents: Categorie[]  = [];
  formMode: FormMode   = 'none';
  editingCat: Categorie | null  = null;
  form!: FormGroup;
  saving  = false;
  loading = true;
 
  constructor(
    private fb: FormBuilder,
    private categorieService: CategorieService
  ) {}
 
  ngOnInit(): void { this.loadCategories(); }
 
  loadCategories(): void {
    this.loading = true;
    this.categorieService.getAll().subscribe({
      next: res => {
        this.categories = res.data ?? [];
        this.parents    = this.categories.filter(c => !c.parent_id);
        this.loading    = false;
      },
      error: () => { this.loading = false; }
    });
  }
 
  openAdd(): void {
    this.editingCat = null;
    this.formMode   = 'add';
    this.form = this.fb.group({
      nom:       ['', Validators.required],
      parent_id: [null],
    });
  }
 
  openEdit(cat: Categorie): void {
    this.editingCat = cat;
    this.formMode   = 'edit';
    this.form = this.fb.group({
      nom:       [cat.nom, Validators.required],
      parent_id: [cat.parent_id],
    });
  }
 
  closeForm(): void { this.formMode = 'none'; this.editingCat = null; }
 
  save(): void {
    if (this.form.invalid) return;
    this.saving = true;
    const data  = this.form.value;
 
    const obs = this.formMode === 'add'
      ? this.categorieService.create(data)
      : this.categorieService.update(this.editingCat!.id, data);
 
    obs.subscribe({
      next: () => { this.saving = false; this.closeForm(); this.loadCategories(); },
      error: () => { this.saving = false; }
    });
  }
 
  onDelete(id: number): void {
    if (!confirm('Supprimer cette catégorie ?')) return;
    this.categorieService.delete(id).subscribe(() => this.loadCategories());
  }
 
  onActivate(id: number): void {
    this.categorieService.activate(id).subscribe(() => this.loadCategories());
  }
 
  onDeactivate(id: number): void {
    this.categorieService.deactivate(id).subscribe(() => this.loadCategories());
  }
 
  getParentName(parentId: number | null): string {
    if (!parentId) return '—';
    return this.parents.find(p => p.id === parentId)?.nom ?? '—';
  }

}
