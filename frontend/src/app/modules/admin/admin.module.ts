import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';


import { AdminRoutingModule } from './admin-routing.module';
import { UsersListComponent } from './components/users-list/users-list.component';
import { AnnoncesMgComponent } from './components/annonces-mg/annonces-mg.component';
import { AvisMgComponent } from './components/avis-mg/avis-mg.component';
import { UserDetailComponent } from './components/user-detail/user-detail.component';
import { CategoriesMgComponent } from './components/categories-mg/categories-mg.component';
import { HomeComponent } from './components/home/home.component';
import { UserAddComponent } from './components/user-add/user-add.component';


@NgModule({
  declarations: [
    UsersListComponent,
    AnnoncesMgComponent,
    AvisMgComponent,
    UserDetailComponent,
    CategoriesMgComponent,
    HomeComponent,
    UserAddComponent
  ],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule,
    AdminRoutingModule
  ]
})
export class AdminModule { }
