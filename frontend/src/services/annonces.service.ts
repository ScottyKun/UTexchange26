import { Injectable } from '@angular/core';
import { ApiResponse } from 'src/models/apiResponse';
import { Observable, map } from 'rxjs';
import { HttpClient,  HttpParams  } from '@angular/common/http';
import { environment } from 'src/environnement';
import { Annonce, AnnonceFilters, AnnonceFormData } from 'src/models/annonce';
import { Photo } from 'src/models/photo';



@Injectable({ providedIn: 'root' })
export class AnnonceService {
 
  private readonly url = `${environment.apiUrl}/annonces`;
 
  constructor(private http: HttpClient) {}
 
 
  getAll(filters?: AnnonceFilters): Observable<ApiResponse<Annonce[]>> {
    let params = new HttpParams();
    if (filters) {
      Object.entries(filters).forEach(([k, v]) => {
        if (v !== undefined && v !== null && v !== '') {
          params = params.set(k, String(v));
        }
      });
    }
    return this.http.get<ApiResponse<any[]>>(this.url, { params }).pipe(
      map(res => ({ ...res, data: res.data?.map((a: any) => new Annonce(a)) ?? [] }))
    );
  }
 
  getMine(): Observable<ApiResponse<Annonce[]>> {
    return this.http.get<ApiResponse<any[]>>(`${this.url}/mine`).pipe(
      map(res => ({ ...res, data: res.data?.map((a: any) => new Annonce(a)) ?? [] }))
    );
  }

  getByUserId(id: number): Observable<ApiResponse<Annonce[]>> {
    return this.http.get<ApiResponse<any[]>>(`${this.url}/user/${id}`).pipe(
      map(res => ({ ...res, data: res.data?.map((a: any) => new Annonce(a)) ?? [] }))
    );
  }
 
  getById(id: number): Observable<ApiResponse<Annonce>> {
    return this.http.get<ApiResponse<any>>(`${this.url}/${id}`).pipe(
      map(res => ({ ...res, data: new Annonce(res.data) }))
    );
  }

 
  create(data: AnnonceFormData): Observable<ApiResponse<{ id: number }>> {
    return this.http.post<ApiResponse<{ id: number }>>(`${this.url}/add`, data);
  }
 
  update(id: number, data: Partial<AnnonceFormData>): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/update/${id}`, data);
  }
 
  delete(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.url}/delete/${id}`);
  }
 
  updateType(id: number, type: string): Observable<ApiResponse<null>> {
    return this.http.patch<ApiResponse<null>>(`${this.url}/${id}/type`, { type });
  }
 
  updateStatus(id: number, status: string): Observable<ApiResponse<null>> {
    return this.http.patch<ApiResponse<null>>(`${this.url}/${id}/status`, { status });
  }
 
 
  uploadPhotos(id: number, files: File[]): Observable<ApiResponse<Photo[]>> {
    const formData = new FormData();
    files.forEach(file => formData.append('photos[]', file));
    return this.http.post<ApiResponse<any[]>>(`${this.url}/${id}/photos`, formData).pipe(
      map(res => ({ ...res, data: res.data?.map((p: any) => new Photo(p)) ?? [] }))
    );
  }
 
  deletePhoto(photoId: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${environment.apiUrl}/photos/${photoId}`);
  }
 
  setCover(photoId: number): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${environment.apiUrl}/photos/${photoId}/cover`, {});
  }
 
  getPhotoUrl(annonceId: number, nomFichier: string): string {
    return `${environment.storageUrl}/${annonceId}/${encodeURIComponent(nomFichier)}`;
  }
}