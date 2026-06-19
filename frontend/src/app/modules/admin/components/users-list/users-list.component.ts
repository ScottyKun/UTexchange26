import { Component, OnInit } from '@angular/core';
import { User } from 'src/models/user';
import { UserService } from 'src/services/users.service';

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.css']
})
export class UsersListComponent implements OnInit {

  users: User[]       = [];
  selectedUser: User | null = null;
  loading             = true;
 
  constructor(private userService: UserService) {}
 
  ngOnInit(): void { this.loadUsers(); }
 
  loadUsers(): void {
    this.loading = true;
    this.userService.getAll().subscribe({
      next: res  => { this.users = res.data ?? []; this.loading = false; },
      error: ()  => { this.loading = false; }
    });
  }
 
  openCard(user: User): void  { this.selectedUser = user; }
  closeCard(): void  { this.selectedUser = null; }
 
  onUpdated(updated: User): void {
    const i = this.users.findIndex(u => u.id === updated.id);
    if (i !== -1) this.users[i] = updated;
    this.selectedUser = updated;
  }
 
  onActivate(id: number): void {
    this.userService.activate(id).subscribe(() => this.loadUsers());
  }
 
  onDeactivate(id: number): void {
    this.userService.deactivate(id).subscribe(() => this.loadUsers());
  }
 
  onDelete(id: number): void {
    if (!confirm('Supprimer cet utilisateur définitivement ?')) return;
    this.userService.delete(id).subscribe(() => {
      this.users = this.users.filter(u => u.id !== id);
      if (this.selectedUser?.id === id) this.selectedUser = null;
    });
  }
 
  getRoleLabel(roleId: number): string {
    const map: Record<number, string> = { 5: 'Administrateur', 2: 'Utilisateur'};
    return map[roleId] ?? 'Utilisateur';
  }
 
  getInitials(user: User): string {
    return ((user.prenom?.[0] ?? '') + (user.nom?.[0] ?? '')).toUpperCase();
  }

  newUser(): User { return new User(); }

}
