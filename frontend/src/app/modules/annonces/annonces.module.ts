import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { AnnoncesRoutingModule } from './annonces-routing.module';
import { AnnonceCreateComponent } from './components/annonce-create/annonce-create.component';
import { AnnonceEditComponent } from './components/annonce-edit/annonce-edit.component';
import { AnnonceItemComponent } from './components/annonce-item/annonce-item.component';
import { AnnonceListComponent } from './components/annonce-list/annonce-list.component';
import { AnnonceShowComponent } from './components/annonce-show/annonce-show.component';
import { FavorisComponent } from './components/favoris/favoris.component';
import { MesAnnoncesComponent } from './components/mes-annonces/mes-annonces.component';


@NgModule({
  declarations: [
    AnnonceCreateComponent,
    AnnonceEditComponent,
    AnnonceItemComponent,
    AnnonceListComponent,
    AnnonceShowComponent,
    FavorisComponent,
    MesAnnoncesComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    AnnoncesRoutingModule
  ]
})
export class AnnoncesModule { }
