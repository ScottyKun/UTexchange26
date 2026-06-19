import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from 'src/services/users.service';
import { AnnonceService } from 'src/services/annonces.service';
import { AuthService } from 'src/services/auth.service';
import { User } from 'src/models/user';
import { Annonce } from 'src/models/annonce';
import { Avis, AvisStats } from 'src/models/avis';

@Component({
  selector: 'app-profil-show',
  templateUrl: './profil-show.component.html',
  styleUrls: ['./profil-show.component.css']
})
export class ProfilShowComponent implements OnInit {

  user?: User;
  annonces: Annonce[] = [];
  avis: Avis[] = [];
  stats?: AvisStats;
  loading = true;
  isOwn = false;
  activeTab: 'annonces' | 'avis' = 'annonces';

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService,
    private annonceService: AnnonceService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    const paramId = this.route.snapshot.paramMap.get('id');
    const userId  = paramId ? +paramId : this.authService.getUserId()!;
    this.isOwn = userId === this.authService.getUserId();

    this.userService.getById(userId).subscribe({
      next: res => {
        this.user = res.data;
        this.loading = false;
      },
      error: () => { this.loading = false; }
    });

    this.annonceService.getMine().subscribe(res => {
      this.annonces = (res.data ?? []);
    });

    this.userService.getAvis(userId).subscribe(res => {
      this.avis  = res.data?.avis ?? [];
      this.stats = res.data?.stats;
    });
  }

}
