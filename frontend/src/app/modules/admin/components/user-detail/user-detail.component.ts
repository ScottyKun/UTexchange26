import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { User } from 'src/models/user';
import { UserService } from 'src/services/users.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

type Tab = 'info' | 'password';

@Component({
  selector: 'app-user-detail',
  templateUrl: './user-detail.component.html',
  styleUrls: ['./user-detail.component.css']
})
export class UserDetailComponent implements OnInit {

  @Input()  user!: User;
  @Output() closed  = new EventEmitter<void>();
  @Output() userUpdated = new EventEmitter<User>();
 
  tab: Tab = 'info';
  editMode  = false;
  saving  = false;
  successMsg  = '';
  errorMsg   = '';
 
  infoForm!: FormGroup;
  pwdForm!: FormGroup;
 
  readonly campuses = ['Belfort','Compiègne','Montbéliard','Sevenans','Tarbes','Troyes'];
 
  constructor(private fb: FormBuilder, private userService: UserService) {}
 
  ngOnInit(): void {
    this.buildForms();
  }
 
  buildForms(): void {
    this.infoForm = this.fb.group({
      nom:    [this.user.nom,    Validators.required],
      prenom: [this.user.prenom, Validators.required],
      email:  [this.user.email,  [Validators.required, Validators.email]],
      campus: [this.user.campus ?? ''],
    });
 
    this.pwdForm = this.fb.group({
      old: ['', Validators.required],
      new: ['', [Validators.required, Validators.minLength(8)]],
    });
  }
 
  // Info
 
  saveInfo(): void {
    if (this.infoForm.invalid) return;
    this.saving = true; this.errorMsg = ''; this.successMsg = '';
 
    this.userService.update(this.user.id, this.infoForm.value).subscribe({
      next: () => {
        const updated = new User({ ...this.user, ...this.infoForm.value });
        this.userUpdated.emit(updated);
        this.successMsg = 'Profil mis à jour.';
        this.editMode   = false;
        this.saving     = false;
      },
      error: () => { this.errorMsg = 'Erreur lors de la mise à jour.'; this.saving = false; }
    });
  }
 
  // Password
 
  savePwd(): void {
    if (this.pwdForm.invalid) return;
    this.saving = true; this.errorMsg = ''; this.successMsg = '';
    const { old, new: newPwd } = this.pwdForm.value;
 
    this.userService.updatePassword(this.user.id, old, newPwd).subscribe({
      next: () => {
        this.successMsg = 'Mot de passe modifié.';
        this.pwdForm.reset();
        this.saving = false;
      },
      error: () => { this.errorMsg = 'Mot de passe actuel incorrect.'; this.saving = false; }
    });
  }
 
  setTab(t: Tab): void {
    this.tab = t;
    this.successMsg = '';
    this.errorMsg   = '';
    this.editMode   = false;
  }
 
  getInitials(): string {
    return ((this.user.prenom?.[0] ?? '') + (this.user.nom?.[0] ?? '')).toUpperCase();
  }
 
  getRoleLabel(): string {
    const map: Record<number, string> = { 5: 'Administrateur', 2: 'Utilisateur' };
    return map[this.user.role_id] ?? 'Utilisateur';
  }
 
  close(): void { this.closed.emit(); }

}
