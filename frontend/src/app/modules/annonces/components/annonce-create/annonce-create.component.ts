import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AnnonceService } from 'src/services/annonces.service';
import { CategorieService } from 'src/services/categories.service';
import { Categorie } from 'src/models/categorie';

@Component({
  selector: 'app-annonce-create',
  templateUrl: './annonce-create.component.html',
  styleUrls: ['./annonce-create.component.css']
})
export class AnnonceCreateComponent implements OnInit {
  form!: FormGroup;
  categories: Categorie[] = [];
  selectedFiles: File[] = [];
  loading = false;
  errorMsg = '';

  constructor(
    private fb: FormBuilder,
    private annonceService: AnnonceService,
    private categorieService: CategorieService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      title:        ['', [Validators.required, Validators.minLength(5)]],
      description:  ['', [Validators.required, Validators.minLength(20)]],
      price:        [0, [Validators.required, Validators.min(0)]],
      type:         ['vente', Validators.required],
      categorie_id: [null, Validators.required],
      location:     ['']
    });
    this.categorieService.getAll().subscribe(res => { this.categories = res.data ?? []; });
  }

  get f() { return this.form.controls; }

  onFilesChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.selectedFiles = Array.from(input.files ?? []);
  }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading = true;
    this.annonceService.create(this.form.value).subscribe({
      next: res => {
        if (res.success && this.selectedFiles.length > 0) {
          this.annonceService.uploadPhotos(res.data.id, this.selectedFiles).subscribe(() => {
            this.router.navigate(['/annonces', res.data.id]);
          });
        } else if (res.success) {
          this.router.navigate(['/annonces', res.data.id]);
        } else {
          this.errorMsg = res.message; this.loading = false;
        }
      },
      error: err => { this.errorMsg = err.error?.message || 'Erreur'; this.loading = false; }
    });
  }

}
