import { Component, OnInit } from '@angular/core';
import { FavoriService } from 'src/services/favoris.service';
import { Annonce } from 'src/models/annonce';

@Component({
  selector: 'app-favoris',
  templateUrl: './favoris.component.html',
  styleUrls: ['./favoris.component.css']
})
export class FavorisComponent implements OnInit {
  annonces: Annonce[] = [];
  loading = true;

  constructor(private favoriService: FavoriService) {}

  ngOnInit(): void {
    this.favoriService.getAll().subscribe({
      next: res => { this.annonces = res.data ?? []; this.loading = false; },
      error: () => { this.loading = false; }
    });
  }

  removeFavori(id: number): void {
    this.favoriService.toggle(id).subscribe(() => {
      this.annonces = this.annonces.filter(a => a.id !== id);
    });
  }

}
