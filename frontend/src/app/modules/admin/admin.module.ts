import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AdminRoutingModule } from './admin-routing.module';
import { UsersListComponent } from './components/users-list/users-list.component';
import { AnnoncesMgComponent } from './components/annonces-mg/annonces-mg.component';
import { AvisMgComponent } from './components/avis-mg/avis-mg.component';


@NgModule({
  declarations: [
    UsersListComponent,
    AnnoncesMgComponent,
    AvisMgComponent
  ],
  imports: [
    CommonModule,
    AdminRoutingModule
  ]
})
export class AdminModule { }
