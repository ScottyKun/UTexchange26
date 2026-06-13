import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from 'src/services/auth.service';
import { ConversationService } from 'src/services/conversations.service';
import { CategorieService } from 'src/services/categories.service';
import { User } from 'src/models/user';
import { Categorie } from 'src/models/categorie';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})
export class NavbarComponent implements OnInit {

  currentUser?: User | null;
  categories: Categorie[] = [];
  unreadCount = 0;
  menuOpen = false;

  constructor(
    private authService: AuthService,
    private convService: ConversationService,
    private categorieService: CategorieService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.categorieService.getAll().subscribe(res => { this.categories = res.data ?? []; });
    if (this.authService.isAuthenticated()) { this.loadUnread(); }
  }

  loadUnread(): void {
    this.convService.getUnreadCount().subscribe(res => {
      this.unreadCount = res.data?.count ?? 0;
    });
  }

  isAuthenticated(): boolean { return this.authService.isAuthenticated(); }
  isAdmin(): boolean { return this.authService.isAdmin(); }

  logout(): void {
    this.authService.logout().subscribe(() => { this.router.navigate(['/login']); });
  }

}
