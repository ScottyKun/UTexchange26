import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from 'src/services/auth.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {

  form!: FormGroup;
  loading = false;
  errorMsg  = '';
  successMsg = '';

  readonly campusList = ['UTBM', 'UTC', 'UTT', 'UTTOP'];

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      prenom:   ['', [Validators.required, Validators.minLength(2)]],
      nom:      ['', [Validators.required, Validators.minLength(2)]],
      email:    ['', [Validators.required, Validators.email]],
      campus:   ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(8)]]
    });
  }

  get f() { return this.form.controls; }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading  = true;
    this.errorMsg = '';

    this.authService.register(this.form.value).subscribe({
      next: res => {
        if (res.success) {
          this.successMsg = 'Compte créé ! Vous pouvez vous connecter.';
          setTimeout(() => this.router.navigate(['/login']), 1800);
        } else {
          this.errorMsg = res.message || 'Erreur lors de l\'inscription';
          this.loading = false;
        }
      },
      error: err => {
        this.errorMsg = err.error?.message || 'Erreur de connexion au serveur';
        this.loading = false;
      }
    });
  }

}
