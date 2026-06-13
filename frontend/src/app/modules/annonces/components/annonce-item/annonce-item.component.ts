import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Annonce } from 'src/models/annonce';
import { environment } from 'src/environnement';

@Component({
  selector: 'app-annonce-item',
  templateUrl: './annonce-item.component.html',
  styleUrls: ['./annonce-item.component.css']
})
export class AnnonceItemComponent{

  @Input() annonce!: Annonce;
  @Input() isFavori = false;
  @Output() toggleFavori = new EventEmitter<number>();

  constructor() { }

  get coverUrl(): string {
    const p = this.annonce.cover;
    if (!p) return '/assets/placeholder.png';
    return `${environment.storageUrl}/${this.annonce.id}/${encodeURIComponent(p.nom_fichier)}`;
  }

  get typeLabel(): { text: string; cls: string } {
    const map: Record<string, { text: string; cls: string }> = {
      vente:    { text: 'Vente',    cls: 'badge-vente' },
      location: { text: 'Location', cls: 'badge-location' },
      don:      { text: 'Don',      cls: 'badge-don' },
      troc:     { text: 'Troc',     cls: 'badge-troc' }
    };
    return map[this.annonce.type] ?? { text: this.annonce.type, cls: '' };
  }

  onFavoriClick(event: Event): void {
    event.preventDefault();
    event.stopPropagation();
    this.toggleFavori.emit(this.annonce.id);
  }

}
