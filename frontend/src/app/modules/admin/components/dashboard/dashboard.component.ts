import { Component } from '@angular/core';

@Component({ selector: 'app-dashboard', templateUrl: './dashboard.component.html' })
export class DashboardComponent {
  stats = { users: 0, annonces: 0, conversations: 0, signalees: 0 };
}
