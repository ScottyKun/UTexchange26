import { Component, OnInit } from '@angular/core';
import { AdminService } from 'src/services/admin.service';
import { AnnonceService } from 'src/services/annonces.service';
import { Annonce } from 'src/models/annonce';

@Component({
  selector: 'app-annonces-mg',
  templateUrl: './annonces-mg.component.html',
  styleUrls: ['./annonces-mg.component.css']
})
export class AnnoncesMgComponent implements OnInit {

  annonces: Annonce[]  = [];
  selected: Annonce | null  = null;
  loading  = true;
 
  constructor(
    private adminService:   AdminService,
    private annonceService: AnnonceService
  ) {}
 
  ngOnInit(): void { this.loadAnnonces(); }
 
  loadAnnonces(): void {
    this.loading = true;
    this.adminService.getAllAnnonces().subscribe({
      next: res  => { this.annonces = res.data ?? []; this.loading = false; },
      error: ()  => { this.loading = false; }
    });
  }
 
  openModal(a: Annonce): void  { this.selected = a; }
  closeModal(): void  { this.selected = null; }
 
  onReport(id: number): void {
    if (!confirm('Signaler cette annonce ?')) return;
    this.adminService.reportAnnonce(id).subscribe(() => this.loadAnnonces());
  }
 
  getCoverUrl(a: any): string | null {
    const cover = a.cover ?? a.photos?.[0];
    return cover ? this.annonceService.getPhotoUrl(a.id, cover.nom_fichier) : null;
  }
 
  getPriceLabel(a: Annonce): string {
    return a.isFree ? 'Gratuit' : `${Number(a.price).toFixed(2).replace('.', ',')} €`;
  }
 
  getTypeLabel(type: string): string {
    const m: Record<string, string> = { vente: 'Vente', don: 'Don', troc: 'Échange', location:'Location' };
    return m[type] ?? type;
  }

}
