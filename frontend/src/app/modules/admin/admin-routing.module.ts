import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { DashboardComponent }      from './components/dashboard/dashboard.component';
import { UsersListComponent }      from './components/users-list/users-list.component';
import { AnnoncesMgComponent }     from './components/annonces-mg/annonces-mg.component';
import { AvisMgComponent }         from './components/avis-mg/avis-mg.component';
import { CategoriesListComponent } from './components/categories-list/categories-list.component';

const routes: Routes = [
  { path: '',           component: DashboardComponent },
  { path: 'users',      component: UsersListComponent },
  { path: 'annonces',   component: AnnoncesMgComponent },
  { path: 'avis',       component: AvisMgComponent },
  { path: 'categories', component: CategoriesListComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
