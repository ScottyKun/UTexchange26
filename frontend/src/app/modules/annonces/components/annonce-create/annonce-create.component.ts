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
  previewUrls: string[] = [];
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
      title: ['', [Validators.required, Validators.minLength(5)]],
      description:  ['', [Validators.required, Validators.minLength(20)]],
      price:  [0, [Validators.required, Validators.min(0)]],
      type:  ['vente', Validators.required],
      categorie_id: [null, Validators.required],
      location: ['']
    });
    this.form.get('type')?.valueChanges.subscribe(() => {
      this.onTypeChange();
    });
    this.categorieService.getAll().subscribe(res => { this.categories = res.data ?? []; });
  }

  onTypeChange(): void {
    const type = this.form.get('type')?.value;

    if (type === 'don') {
      this.form.get('price')?.setValue(0);
      this.form.get('price')?.disable();   // optionnel
    } else {
      this.form.get('price')?.enable();
    }
  }

  get f() { return this.form.controls; }

  onFilesChange(event: Event): void {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    this.addFiles(files);
  }

  onDrop(event: DragEvent): void {
    event.preventDefault();
    const files = Array.from(event.dataTransfer?.files ?? []);
    this.addFiles(files);
  }

  onDragOver(event: DragEvent): void {
    event.preventDefault();
  }

  private addFiles(files: File[]): void {
    const remaining = 8 - this.selectedFiles.length;

    const newFiles = files.slice(0, remaining);

    this.selectedFiles.push(...newFiles);

    newFiles.forEach(file => {
      const reader = new FileReader();
      reader.onload = () => {
        this.previewUrls.push(reader.result as string);
      };
      reader.readAsDataURL(file);
    });
  }

  removeFile(index: number): void {
    this.selectedFiles.splice(index, 1);
    this.previewUrls.splice(index, 1);
  }

  getPreview(file: File): string {
    return URL.createObjectURL(file);
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
