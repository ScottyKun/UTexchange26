import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { AdminRoutingModule } from './admin-routing.module';

import { DashboardComponent }      from './components/dashboard/dashboard.component';
import { UsersListComponent }      from './components/users-list/users-list.component';
import { AnnoncesMgComponent }     from './components/annonces-mg/annonces-mg.component';
import { AvisMgComponent }         from './components/avis-mg/avis-mg.component';
import { CategoriesListComponent } from './components/categories-list/categories-list.component';

@NgModule({
  declarations: [
    DashboardComponent,
    UsersListComponent,
    AnnoncesMgComponent,
    AvisMgComponent,
    CategoriesListComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    AdminRoutingModule
  ]
})
export class AdminModule { }
