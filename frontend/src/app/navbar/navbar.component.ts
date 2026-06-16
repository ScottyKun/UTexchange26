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

  categories: Categorie[] = [];
  activeCatId: number | null = null;
  activeType: string | null = null;


  constructor(
    private categorieService: CategorieService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.categorieService.getAll().subscribe(res => { this.categories = res.data ?? []; });

    this.route.queryParams.subscribe(params => {
      this.activeCatId = params['cat_id'] ? +params['cat_id'] : null;
      this.activeType = params['type'] ?? null;
    });
  }

  isAllActive(): boolean {
    return !this.activeCatId && !this.activeType;
  }

  isDonActive(): boolean {
    return this.activeType === 'don';
  }

  isCatActive(id: number): boolean {
    return this.activeCatId === id;
  }


}
