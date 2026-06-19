import { Component, Input } from '@angular/core';
import { Annonce } from 'src/models/annonce';

interface CategorySection {
  id: number;
  nom: string;
  icone?: string;
  annonces: Annonce[];
}

@Component({
  selector: 'app-annonce-card',
  templateUrl: './annonce-card.component.html',
})
export class AnnonceCardComponent {
  @Input() section!: CategorySection;

  formatPrice(annonce: Annonce): string {
    if (annonce.isFree)  return 'Gratuit';
    if (annonce.isTroc) return 'Troc';
    return annonce.price.toFixed(2).replace('.', ',') + ' €';
  }

  priceClass(annonce: Annonce): string {
    if (annonce.isFree)  return 'annonce-card-h__price annonce-card-h__price--free';
    if (annonce.isTroc) return 'annonce-card-h__price annonce-card-h__price--troc';
    return 'annonce-card-h__price';
  }
}