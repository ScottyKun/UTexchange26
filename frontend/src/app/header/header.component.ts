import { Component, OnInit } from '@angular/core';
import { User } from 'src/models/user';
import { AuthService } from 'src/services/auth.service';
import { ConversationService } from 'src/services/conversations.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit {

  currentUser: User | null = null;
  unreadCount = 0;
  searchQuery = '';
  private interval: any;

  constructor(
    private authService: AuthService,
    private convService: ConversationService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    if (this.isAuthenticated()) {
      this.loadUnread();

      this.interval = setInterval(() => {
        this.loadUnread();
      }, 3000);
    }
  }

  loadUnread(): void {
    this.convService.getUnreadCount().subscribe(res => {
      this.unreadCount = res.data?.count ?? 0;
    });
  }

  onSearch(): void {
    this.router.navigate(['/annonces'], {
      queryParams: this.searchQuery.trim() ? { search: this.searchQuery.trim() } : {}
    });
  }

  isAuthenticated(): boolean { return this.authService.isAuthenticated(); }
  isAdmin(): boolean { return this.authService.isAdmin(); }

  logout(): void {
    this.authService.logout().subscribe(() => {
      this.router.navigate(['/']);
    });
  }

  ngOnDestroy(): void {
    if (this.interval) clearInterval(this.interval);
  }

}
