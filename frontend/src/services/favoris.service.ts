import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from 'src/environnement';
import { HttpClient } from '@angular/common/http';
import { ApiResponse } from 'src/models/apiResponse';
import { Annonce } from 'src/models/annonce';



@Injectable({ providedIn: 'root' })
export class FavoriService {
 
  private readonly url = environment.apiUrl;
 
  constructor(private http: HttpClient) {}
 
  getAll(): Observable<ApiResponse<Annonce[]>> {
    return this.http.get<ApiResponse<any[]>>(`${this.url}/favoris`).pipe(
      map(res => ({ ...res, data: res.data?.map((a: any) => new Annonce(a)) ?? [] }))
    );
  }
 
  toggle(annonceId: number): Observable<ApiResponse<{ action: 'added' | 'removed' }>> {
    return this.http.post<ApiResponse<{ action: 'added' | 'removed' }>>(
      `${this.url}/annonces/${annonceId}/favori`, {}
    );
  }
 
  check(annonceId: number): Observable<ApiResponse<{ favori: boolean }>> {
    return this.http.get<ApiResponse<{ favori: boolean }>>(
      `${this.url}/annonces/${annonceId}/is-favori`
    );
  }
}
