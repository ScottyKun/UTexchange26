import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from 'src/guards/auth.guard';

import { AnnonceListComponent }   from './components/annonce-list/annonce-list.component';
import { AnnonceShowComponent }   from './components/annonce-show/annonce-show.component';
import { AnnonceCreateComponent } from './components/annonce-create/annonce-create.component';
import { AnnonceEditComponent }   from './components/annonce-edit/annonce-edit.component';
import { MesAnnoncesComponent }   from './components/mes-annonces/mes-annonces.component';
import { FavorisComponent }       from './components/favoris/favoris.component';

const routes: Routes = [
  { path: '',  component: AnnonceListComponent },
  { path: ':id', component: AnnonceShowComponent },
  { path: 'new/create', component: AnnonceCreateComponent, canActivate: [AuthGuard] },
  { path: ':id/edit',  component: AnnonceEditComponent,   canActivate: [AuthGuard] },
  { path: 'user/mine', component: MesAnnoncesComponent,   canActivate: [AuthGuard] },
  { path: 'user/favoris', component: FavorisComponent,    canActivate: [AuthGuard] }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AnnoncesRoutingModule { }
