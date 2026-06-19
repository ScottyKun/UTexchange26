import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, RouterStateSnapshot} from '@angular/router';
import { Router } from '@angular/router';
import { AuthService } from 'src/services/auth.service';


@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
 
  constructor(private authService: AuthService, private router: Router) {}
 
  canActivate(
    _route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): boolean {
    if (this.authService.isAuthenticated()) {
      return true;
    }
    // on sauvegarde l'URL tentée pour redirection post-login
    this.router.navigate(['/login'], {
      queryParams: { returnUrl: state.url }
    });
    return false;
  }
}
