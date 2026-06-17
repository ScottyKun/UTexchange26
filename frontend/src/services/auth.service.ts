import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from 'src/environnement';
import { User } from 'src/models/user';
import { ApiResponse } from 'src/models/apiResponse';
import { Observable, tap, map } from 'rxjs';

export interface LoginCredentials {
  email: string;
  password: string;
}
 
export interface RegisterData {
  nom: string;
  prenom: string;
  email: string;
  password: string;
  campus?: string;
}
 
export interface LoginResponse {
  token: string;
  user: User;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
 
  private readonly url = `${environment.apiUrl}/auth`;
 
  constructor(private http: HttpClient) {}
 
  login(credentials: LoginCredentials): Observable<ApiResponse<LoginResponse>> {
    return this.http.post<ApiResponse<any>>(`${this.url}/login`, credentials).pipe(
      map(res => ({
        ...res,
        data: res.data ? { token: res.data.token, user: new User(res.data.user) } : res.data
      })),
      tap(res => {
        if (res.success && res.data) {
          localStorage.setItem('token', res.data.token);
          localStorage.setItem('user', JSON.stringify(res.data.user));
        }
      })
    );
  }
 
  register(data: RegisterData): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.url}/register`, data);
  }
 
  logout(): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.url}/logout`, {}).pipe(
      tap(() => this.clearSession())
    );
  }
 
  me(): Observable<ApiResponse<User>> {
    return this.http.get<ApiResponse<any>>(`${this.url}/me`).pipe(
      map(res => ({ ...res, data: new User(res.data) }))
    );
  }
 
  //session locale
 
  isAuthenticated(): boolean {
    const token = this.getToken();
    if (!token) return false;
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return payload.exp > Math.floor(Date.now() / 1000);
    } catch {
      return false;
    }
  }
 
  getToken(): string | null {
    return localStorage.getItem('token');
  }
 
  getCurrentUser(): User | null {
    const raw = localStorage.getItem('user');
    return raw ? new User(JSON.parse(raw)) : null;
  }
 
  getUserId(): number | null {
    return this.getCurrentUser()?.id ?? null;
  }
 
  isAdmin(): boolean {
    return this.getCurrentUser()?.isAdmin ?? false;
  }
 
  clearSession(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  }
}
