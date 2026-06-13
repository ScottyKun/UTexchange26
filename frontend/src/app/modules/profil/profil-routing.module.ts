import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ProfilEditComponent } from './components/profil-edit/profil-edit.component';
import { ProfilShowComponent } from './components/profil-show/profil-show.component';

const routes: Routes = [
  // user connecté (/profil)
  { path: '',       component: ProfilShowComponent },
  { path: 'edit',   component: ProfilEditComponent },
  // acces publid (/profil/:id)
  { path: ':id',    component: ProfilShowComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ProfilRoutingModule { }
