import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from 'src/guards/auth.guard';
import { AdminGuard } from 'src/guards/admin.guard';

const routes: Routes = [
  { path: '', redirectTo: 'annonces', pathMatch: 'full' },
  {
    path: '',
    loadChildren: () => import('./modules/auth/auth.module').then(m => m.AuthModule)
  },
  {
    path: 'annonces',
    loadChildren: () => import('./modules/annonces/annonces.module').then(m => m.AnnoncesModule)
  },
  {
    path: 'profil',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/profil/profil.module').then(m => m.ProfilModule)
  },
  {
    path: 'conversations',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/conversations/conversations.module').then(m => m.ConversationsModule)
  },
  {
    path: 'admin',
    canActivate: [AuthGuard, AdminGuard],
    loadChildren: () => import('./modules/admin/admin.module').then(m => m.AdminModule)
  },
  { path: '**', redirectTo: 'annonces' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { scrollPositionRestoration: 'top' })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
