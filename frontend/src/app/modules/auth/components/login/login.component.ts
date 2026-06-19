import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService } from 'src/services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  form!: FormGroup;
  loading = false;
  errorMsg = '';
  returnUrl = '/annonces';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });

    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] ?? '/annonces';

    // Déjà connecté = redirect
    if (this.authService.isAuthenticated()) {
      this.router.navigateByUrl(this.returnUrl);
    }
  }

  get f() { return this.form.controls; }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading  = true;
    this.errorMsg = '';

    this.authService.login({ email: this.f['email'].value, password: this.f['password'].value })
      .subscribe({
        next: res => {
          if (res.success) {
            this.router.navigateByUrl(this.returnUrl);
          } else {
            this.errorMsg = res.message || 'Identifiants incorrects';
            this.loading = false;
          }
        },
        error: err => {
          this.errorMsg = err.error?.message || 'Erreur de connexion';
          this.loading = false;
        }
      });
  }

}
