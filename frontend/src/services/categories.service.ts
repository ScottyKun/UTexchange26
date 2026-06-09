import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from 'src/environnement';
import { HttpClient } from '@angular/common/http';
import { ApiResponse } from 'src/models/apiResponse';
import { Categorie } from 'src/models/categorie';



@Injectable({ providedIn: 'root' })
export class CategorieService {
 
  private readonly url = `${environment.apiUrl}/categories`;
 
  constructor(private http: HttpClient) {}
 
  getAll(): Observable<ApiResponse<Categorie[]>> {
    return this.http.get<ApiResponse<any[]>>(this.url).pipe(
      map(res => ({ ...res, data: res.data?.map((c: any) => new Categorie(c)) ?? [] }))
    );
  }
 
  getById(id: number): Observable<ApiResponse<Categorie>> {
    return this.http.get<ApiResponse<any>>(`${this.url}/${id}`).pipe(
      map(res => ({ ...res, data: new Categorie(res.data) }))
    );
  }
 
  create(data: Partial<Categorie>): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.url}/add`, data);
  }
 
  update(id: number, data: Partial<Categorie>): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/update/${id}`, data);
  }
 
  delete(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.url}/delete/${id}`);
  }
 
  activate(id: number): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${id}/activate`, {});
  }
 
  deactivate(id: number): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${id}/deactivate`, {});
  }
}