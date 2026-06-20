import { Component, OnInit, Input, Output, EventEmitter  } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { UserService } from 'src/services/users.service';
import { AuthService } from 'src/services/auth.service';
import { User } from 'src/models/user';

@Component({
  selector: 'app-profil-edit',
  templateUrl: './profil-edit.component.html',
  styleUrls: ['./profil-edit.component.css']
})
export class ProfilEditComponent implements OnInit {

  infoForm!: FormGroup;
  pwdForm!: FormGroup;
  user?: User;
  loading = true;
  saving  = false;
  savingPwd = false;
  successMsg = '';
  errorMsg   = '';
  pwdError   = '';

  @Input()
  mode!: 'info' | 'password';

  @Output()
  close = new EventEmitter<void>();

  readonly campusList = ['UTBM', 'UTC', 'UTT', 'UTTOP'];

   constructor(
    private fb: FormBuilder,
    private userService: UserService,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    const id = this.authService.getUserId()!;
    this.userService.getById(id).subscribe(res => {
      this.user = res.data;
      this.infoForm = this.fb.group({
        prenom: [this.user.prenom, Validators.required],
        nom:    [this.user.nom,    Validators.required],
        campus: [this.user.campus, Validators.required]
      });
      this.loading = false;
    });

    this.pwdForm = this.fb.group({
      old:     ['', Validators.required],
      newPwd:  ['', [Validators.required, Validators.minLength(8)]],
      confirm: ['', Validators.required]
    });
  }

  get fi() { return this.infoForm.controls; }
  get fp() { return this.pwdForm.controls; }

  saveInfo(): void {
    if (this.infoForm.invalid) { this.infoForm.markAllAsTouched(); return; }
    this.saving = true;
    this.userService.update(this.user!.id, this.infoForm.value).subscribe({
      next: () => { this.successMsg = 'Profil mis à jour.'; this.saving = false; this.close.emit(); },
      error: () => { this.errorMsg = 'Erreur lors de la mise à jour'; this.saving = false; }
    });
  }

  savePassword(): void {
    this.pwdError = '';
    if (this.fp['newPwd'].value !== this.fp['confirm'].value) {
      this.pwdError = 'Les mots de passe ne correspondent pas'; return;
    }
    this.savingPwd = true;
    this.userService.updatePassword(this.user!.id, this.fp['old'].value, this.fp['newPwd'].value)
      .subscribe({
        next: () => { this.pwdForm.reset(); this.successMsg = 'Mot de passe modifié.'; this.savingPwd = false; this.close.emit(); },
        error: err => { this.pwdError = err.error?.message || 'Erreur'; this.savingPwd = false; }
      });
  }

}
