import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AnnonceService } from 'src/services/annonces.service';
import { ConversationService } from 'src/services/conversations.service';
import { FavoriService } from 'src/services/favoris.service';
import { AuthService } from 'src/services/auth.service';
import { Annonce } from 'src/models/annonce';
import { UserService } from 'src/services/users.service';
import { User } from 'src/models/user';
import { environment } from 'src/environnement';

@Component({
  selector: 'app-annonce-show',
  templateUrl: './annonce-show.component.html',
  styleUrls: ['./annonce-show.component.css']
})
export class AnnonceShowComponent implements OnInit {

  annonce?: Annonce;
  user?: User;
  loading   = true;
  errorMsg  = '';
  isFavori  = false;
  activePhotoIndex = 0;
  contactLoading = false;

  showTypeModal = false;
  showStatusModal = false;

  selectedType = '';
  selectedStatus = '';

  typeLoading = false;
  statusLoading = false;

  readonly types = [
    { value: 'vente', label: 'Vente' },
    { value: 'location', label: 'Location' },
    { value: 'don', label: 'Don' },
    { value: 'troc', label: 'Troc' }
  ];

  readonly statuses = [
    { value: 'active', label: 'Active' },
    { value: 'vendu', label: 'Vendue' },
    { value: 'expire', label: 'Expirée' },
    { value: 'signale', label: 'Signalée' },
    { value: 'draft', label: 'Brouillon' }
  ];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private annonceService: AnnonceService,
    private convService: ConversationService,
    private favoriService: FavoriService,
    private userService: UserService,
    public authService: AuthService
  ) {}

  ngOnInit(): void {
    const id = +this.route.snapshot.paramMap.get('id')!;
    this.annonceService.getById(id).subscribe({
      next: res => {
        this.annonce = res.data;
        this.loading = false;
        if (this.authService.isAuthenticated()) { this.checkFavori(id); }
        
        this.userService.getById(this.annonce.user_id).subscribe({next: r => {this.user = r.data}})
      },
      error: () => { this.errorMsg = 'Annonce introuvable'; this.loading = false; }
    });
  }

  checkFavori(id: number): void {
    this.favoriService.check(id).subscribe(res => { this.isFavori = res.data?.favori ?? false; });
  }

  toggleFavori(): void {
    if (!this.authService.isAuthenticated()) { this.router.navigate(['/login']); return; }
    this.favoriService.toggle(this.annonce!.id).subscribe(res => {
      this.isFavori = res.data?.action === 'added';
    });
  }

  contacter(): void {
    if (!this.authService.isAuthenticated()) { this.router.navigate(['/login']); return; }
    this.contactLoading = true;
    this.convService.create(this.annonce!.id).subscribe({
      next: res => { this.router.navigate(['/conversations', res.data.id]); },
      error: err => {
        // La conv existe déjà → rediriger vers conversations
        if (err.status === 409 && err.error?.data?.id) {
          this.router.navigate(['/conversations', err.error.data.id]);
        }
        this.contactLoading = false;
      }
    });
  }

  photoUrl(nomFichier: string): string {
    return `${environment.storageUrl}/${this.annonce!.id}/${encodeURIComponent(nomFichier)}`;
  }

  isOwner(): boolean {
    return this.authService.getUserId() === this.annonce?.user_id;
  }

  changeType(): void {
    this.selectedType = this.annonce!.type;
    this.showTypeModal = true;
  }

  changeStatus(): void {
    this.selectedStatus = this.annonce!.status;
    this.showStatusModal = true;
  }

  saveType(): void {
    if (!this.annonce) return;
    this.typeLoading = true;
    this.annonceService.updateType(this.annonce.id, this.selectedType)
      .subscribe({
        next: () => {
          this.annonce!.type = this.selectedType as any;
          this.showTypeModal = false;
          this.typeLoading = false;
        },
        error: () => {
          this.typeLoading = false;
        }
      });
  }

  saveStatus(): void {
    if (!this.annonce) return;
    this.statusLoading = true;
    this.annonceService.updateStatus(this.annonce.id, this.selectedStatus)
      .subscribe({
        next: () => {
          this.annonce!.status = this.selectedStatus as any;
          this.showStatusModal = false;
          this.statusLoading = false;
        },
        error: () => {
          this.statusLoading = false;
        }
      });
  }

}
