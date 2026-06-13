import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';

import { ProfilRoutingModule } from './profil-routing.module';
import { AnnonceListItemComponent } from './components/annonce-list-item/annonce-list-item.component';
import { AvisItemComponent } from './components/avis-item/avis-item.component';
import { ProfilEditComponent } from './components/profil-edit/profil-edit.component';
import { ProfilShowComponent } from './components/profil-show/profil-show.component';


@NgModule({
  declarations: [
    AnnonceListItemComponent,
    AvisItemComponent,
    ProfilEditComponent,
    ProfilShowComponent
  ],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    ProfilRoutingModule
  ]
})
export class ProfilModule { }
