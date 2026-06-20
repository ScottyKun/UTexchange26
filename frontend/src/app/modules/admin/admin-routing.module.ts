import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './components/home/home.component';
import { UsersListComponent } from './components/users-list/users-list.component';
import { AnnoncesMgComponent } from './components/annonces-mg/annonces-mg.component';
import { AvisMgComponent } from './components/avis-mg/avis-mg.component';
import { CategoriesMgComponent } from './components/categories-mg/categories-mg.component';
import { StatsComponent } from './components/stats/stats.component';

const routes: Routes = [
  { path: '',             redirectTo: 'dashboard', pathMatch: 'full' },
  { path: 'dashboard',    component: HomeComponent },
  { path: 'utilisateurs', component: UsersListComponent },
  { path: 'annonces',     component: AnnoncesMgComponent },
  { path: 'avis',         component: AvisMgComponent },
  { path: 'categories',   component: CategoriesMgComponent },
  { path: 'stats',        component: StatsComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
