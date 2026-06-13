import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AnnonceService } from 'src/services/annonces.service';
import { CategorieService } from 'src/services/categories.service';
import { AuthService } from 'src/services/auth.service';
import { Categorie } from 'src/models/categorie';
import { Annonce } from 'src/models/annonce';

@Component({
  selector: 'app-annonce-edit',
  templateUrl: './annonce-edit.component.html',
  styleUrls: ['./annonce-edit.component.css']
})
export class AnnonceEditComponent implements OnInit {
  
  form!: FormGroup;
  annonce?: Annonce;
  categories: Categorie[] = [];
  selectedFiles: File[] = [];
  loading  = true;
  saving   = false;
  errorMsg = '';

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private annonceService: AnnonceService,
    private categorieService: CategorieService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    const id = +this.route.snapshot.paramMap.get('id')!;
    this.categorieService.getAll().subscribe(res => { this.categories = res.data ?? []; });
    this.annonceService.getById(id).subscribe({
      next: res => {
        this.annonce = res.data;
        // Vérifier que c'est bien le propriétaire
        if (this.annonce.user_id !== this.authService.getUserId() && !this.authService.isAdmin()) {
          this.router.navigate(['/annonces']);
          return;
        }
        this.initForm();
        this.loading = false;
      },
      error: () => { this.loading = false; this.errorMsg = 'Annonce introuvable'; }
    });
  }

  initForm(): void {
    const a = this.annonce!;
    this.form = this.fb.group({
      title:        [a.title, [Validators.required, Validators.minLength(5)]],
      description:  [a.description, [Validators.required, Validators.minLength(20)]],
      price:        [a.price, [Validators.required, Validators.min(0)]],
      type:         [a.type, Validators.required],
      categorie_id: [a.categorie_id, Validators.required],
      location:     [a.location]
    });
  }

  get f() { return this.form.controls; }

  onFilesChange(event: Event): void {
    this.selectedFiles = Array.from((event.target as HTMLInputElement).files ?? []);
  }

  deletePhoto(photoId: number): void {
    this.annonceService.deletePhoto(photoId).subscribe(() => {
      this.annonce!.photos = this.annonce!.photos.filter(p => p.id !== photoId);
    });
  }

  onSubmit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving = true;
    this.annonceService.update(this.annonce!.id, this.form.value).subscribe({
      next: res => {
        if (res.success && this.selectedFiles.length > 0) {
          this.annonceService.uploadPhotos(this.annonce!.id, this.selectedFiles).subscribe(() => {
            this.router.navigate(['/annonces', this.annonce!.id]);
          });
        } else {
          this.router.navigate(['/annonces', this.annonce!.id]);
        }
      },
      error: err => { this.errorMsg = err.error?.message || 'Erreur'; this.saving = false; }
    });
  }

}
