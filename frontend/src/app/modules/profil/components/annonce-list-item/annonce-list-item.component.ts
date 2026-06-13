import { Component, Input } from '@angular/core';
import { Annonce } from 'src/models/annonce';
import { environment } from 'src/environnement';

@Component({
  selector: 'app-annonce-list-item',
  templateUrl: './annonce-list-item.component.html',
  styleUrls: ['./annonce-list-item.component.css']
})
export class AnnonceListItemComponent  {

  @Input() annonce!: Annonce;

  constructor() { }

 get coverUrl(): string {
    const p = this.annonce.cover;
    if (!p) return '/assets/placeholder.png';
    return `${environment.storageUrl}/${this.annonce.id}/${encodeURIComponent(p.nom_fichier)}`;
  }
}
