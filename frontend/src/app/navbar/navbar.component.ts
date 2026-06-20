import { Component, OnInit } from '@angular/core';
import { CategorieService } from 'src/services/categories.service';
import { ActivatedRoute } from '@angular/router';
import { Categorie } from 'src/models/categorie';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})
export class NavbarComponent implements OnInit {

  parents: Categorie[] = [];
  activeCatId: number | null = null;
  activeType: string | null = null;
  childrenMap: Map<number, Categorie[]> = new Map();


  constructor(
    private categorieService: CategorieService,
    private route: ActivatedRoute
  ) {}

   ngOnInit(): void {
    this.categorieService.getAll().subscribe(res => {
      const all = res.data ?? [];

      // Séparation parents et enfants
      this.parents = all.filter(c => !c.parent_id);

      all.filter(c => !!c.parent_id).forEach(c => {
        const list = this.childrenMap.get(c.parent_id!) ?? [];
        list.push(c);
        this.childrenMap.set(c.parent_id!, list);
      });
    });

    this.route.queryParams.subscribe(params => {
      this.activeCatId = params['cat_id'] ? +params['cat_id'] : null;
      this.activeType  = params['type']   ?? null;
    });
  }

  getChildren(parentId: number): Categorie[] {
    return this.childrenMap.get(parentId) ?? [];
  }

  hasChildren(parentId: number): boolean {
    return (this.childrenMap.get(parentId)?.length ?? 0) > 0;
  }

  isAllActive(): boolean   { return !this.activeCatId && !this.activeType; }
  isDonActive(): boolean   { return this.activeType === 'don'; }

  isCatActive(id: number): boolean {
    if (this.activeCatId === id) return true;
    // Actif si un enfant de cette catégorie est sélectionné
    return this.getChildren(id).some(c => c.id === this.activeCatId);
  }


}
