import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from 'src/environnement';
import { ApiResponse } from 'src/models/apiResponse';
import { Annonce } from 'src/models/annonce';
import { Avis } from 'src/models/avis';

@Injectable({
  providedIn: 'root'
})
export class AdminService {

  private readonly url = `${environment.apiUrl}/admin`;

  constructor(private http: HttpClient) { }

   getAllAnnonces(): Observable<ApiResponse<Annonce[]>> {
    return this.http.get<ApiResponse<any[]>>(`${this.url}/annonces`).pipe(
      map(res => ({ ...res, data: res.data?.map((a: any) => new Annonce(a)) ?? [] }))
    );
  }
 
  reportAnnonce(id: number): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/annonces/${id}/report`, {});
  }
 
  getAllAvis(): Observable<ApiResponse<Avis[]>> {
    return this.http.get<ApiResponse<any[]>>(`${this.url}/avis`).pipe(
      map(res => ({ ...res, data: res.data?.map((a: any) => new Avis(a)) ?? [] }))
    );
  }
 
  activateAvis(convId: string, avisId: string): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(
      `${this.url}/conversations/${convId}/avis/${avisId}/activate`, {}
    );
  }
 
  deactivateAvis(convId: string, avisId: string): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(
      `${this.url}/conversations/${convId}/avis/${avisId}/deactivate`, {}
    );
  }
}
