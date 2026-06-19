import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/services/auth.service';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {

  prenom   = '';
 
  
  cards = [
    { label: 'Utilisateurs',  key: 'nb_users',         icon: 'fa-users',    color: '#0056b3', bg: '#eff6ff' },
    { label: 'Annonces',      key: 'nb_annonces',       icon: 'fa-tag',      color: '#16a34a', bg: '#dcfce7' },
    { label: 'Conversations', key: 'nb_conversations',  icon: 'fa-comments', color: '#7c3aed', bg: '#ede9fe' },
    { label: 'Avis',          key: 'nb_avis',           icon: 'fa-star',     color: '#f59e0b', bg: '#fef9c3' },
  ];
 
  links = [
    { route: '/admin/utilisateurs', icon: 'fa-users',     label: 'Gérer les utilisateurs', color: '#0056b3' },
    { route: '/admin/annonces',     icon: 'fa-tag',       label: 'Gérer les annonces',     color: '#16a34a' },
    { route: '/admin/categories',   icon: 'fa-folder',    label: 'Gérer les catégories',   color: '#7c3aed' },
    { route: '/admin/avis',         icon: 'fa-star',      label: 'Gérer les avis',         color: '#f59e0b' },
    { route: '/admin/stats',        icon: 'fa-chart-bar', label: 'Statistiques',           color: '#0891b2' },
  ];
 
  constructor(
    private authService:  AuthService
  ) {}
 
  ngOnInit(): void {
    this.prenom = this.authService.getCurrentUser()?.prenom ?? 'Admin';
  }
 
 
}
