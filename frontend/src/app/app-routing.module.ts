import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from 'src/guards/auth.guard';
import { AdminGuard } from 'src/guards/admin.guard';

const routes: Routes = [
  // Page d'accueil
  {
    path: 'home',
    loadChildren: () => import('./modules/home/home.module').then(m => m.HomeModule)
  },
  // Racine -> redirige vers /home
  { path: '', redirectTo: 'home', pathMatch: 'full' },

  // Auth
  {
    path: '',
    loadChildren: () => import('./modules/auth/auth.module').then(m => m.AuthModule)
  },
  // Annonces
  {
    path: 'annonces',
    loadChildren: () => import('./modules/annonces/annonces.module').then(m => m.AnnoncesModule)
  },
  // Profil
  {
    path: 'profil',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/profil/profil.module').then(m => m.ProfilModule)
  },
  // Conversations
  {
    path: 'conversations',
    canActivate: [AuthGuard],
    loadChildren: () => import('./modules/conversations/conversations.module').then(m => m.ConversationsModule)
  },
  // Admin
  {
    path: 'admin',
    canActivate: [AuthGuard, AdminGuard],
    loadChildren: () => import('./modules/admin/admin.module').then(m => m.AdminModule)
  },
  { path: '**', redirectTo: 'home' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { scrollPositionRestoration: 'top' })],
  exports: [RouterModule]
})
export class AppRoutingModule {}
