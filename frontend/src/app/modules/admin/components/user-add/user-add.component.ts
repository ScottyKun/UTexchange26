import { Component, Output, EventEmitter } from '@angular/core';
import { AuthService } from 'src/services/auth.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-user-add',
  templateUrl: './user-add.component.html',
  styleUrls: ['./user-add.component.css']
})
export class UserAddComponent {

  @Output() closed      = new EventEmitter<void>();
  @Output() userCreated = new EventEmitter<void>();

  form!: FormGroup;
  loading = false;
  errorMsg  = '';
  successMsg = '';

  readonly campusList = ['Belfort','Compiègne','Montbéliard','Sevenans','Tarbes','Troyes'];

  constructor(private fb: FormBuilder, private authService: AuthService) {
    this.form = this.fb.group({
      prenom:   ['', Validators.required],
      nom:      ['', Validators.required],
      email:    ['', [Validators.required, Validators.email]],
      campus:   ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(8)]],
    });
  }

  get f() { return this.form.controls; }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading = true; this.errorMsg = ''; this.successMsg = '';

    this.authService.register(this.form.value).subscribe({
      next: () => {
        this.successMsg = 'Utilisateur créé avec succès.';
        this.loading    = false;
        this.form.reset();
        this.userCreated.emit();
        setTimeout(() => this.closed.emit(), 1200);
      },
      error: () => {
        this.errorMsg = 'Cet email est déjà utilisé.';
        this.loading  = false;
      }
    });
  }

  close(): void { this.closed.emit(); }

}
