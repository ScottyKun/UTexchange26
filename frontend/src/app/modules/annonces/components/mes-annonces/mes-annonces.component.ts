import { Component, OnInit } from '@angular/core';
import { AnnonceService } from 'src/services/annonces.service';
import { Annonce } from 'src/models/annonce'
import { environment } from 'src/environnement';

@Component({
  selector: 'app-mes-annonces',
  templateUrl: './mes-annonces.component.html',
  styleUrls: ['./mes-annonces.component.css']
})
export class MesAnnoncesComponent implements OnInit {

  annonces: Annonce[] = [];
  loading = true;
  errorMsg = '';

  constructor(private annonceService: AnnonceService) {}

  ngOnInit(): void {
    this.annonceService.getMine().subscribe({
      next: res => { this.annonces = res.data ?? []; this.loading = false; },
      error: () => { this.errorMsg = 'Erreur de chargement'; this.loading = false; }
    });
  }

  deleteAnnonce(id: number): void {
    if (!confirm('Supprimer cette annonce ?')) return;
    this.annonceService.delete(id).subscribe(() => {
      this.annonces = this.annonces.filter(a => a.id !== id);
    });
  }

  getCoverUrl(annonce: Annonce): string {
    const p = annonce.cover;
    if (!p) return '/assets/placeholder.png';

   return `${environment.storageUrl}/${annonce.id}/${encodeURIComponent(p.nom_fichier)}`;
  }

}
