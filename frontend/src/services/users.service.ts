import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from 'src/environnement';
import { HttpClient } from '@angular/common/http';
import { ApiResponse } from 'src/models/apiResponse';
import { Avis,AvisStats } from 'src/models/avis';
import { User } from 'src/models/user';


export interface UserAvisResponse {
  stats: AvisStats;
  avis: Avis[];
}
 
@Injectable({ providedIn: 'root' })
export class UserService {
 
  private readonly url = `${environment.apiUrl}/users`;
 
  constructor(private http: HttpClient) {}
 
  getAll(): Observable<ApiResponse<User[]>> {
    return this.http.get<ApiResponse<any[]>>(this.url).pipe(
      map(res => ({ ...res, data: res.data?.map((u: any) => new User(u)) ?? [] }))
    );
  }
 
  getById(id: number): Observable<ApiResponse<User>> {
    return this.http.get<ApiResponse<any>>(`${this.url}/${id}`).pipe(
      map(res => ({ ...res, data: new User(res.data) }))
    );
  }

  create(data: Partial<User>): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.url}/create`, data);
  }
 
  update(id: number, data: Partial<User>): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/update/${id}`, data);
  }
 
  delete(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.url}/delete/${id}`);
  }
 
  updatePassword(id: number, oldPwd: string, newPwd: string): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${id}/password`, {
      old: oldPwd,
      new: newPwd
    });
  }
 
  activate(id: number): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${id}/activate`, {});
  }
 
  deactivate(id: number): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${id}/deactivate`, {});
  }
 
  getAvis(id: number): Observable<ApiResponse<UserAvisResponse>> {
    return this.http.get<ApiResponse<any>>(`${this.url}/${id}/avis`).pipe(
      map(res => ({
        ...res,
        data: {
          stats: new AvisStats(res.data?.stats),
          avis:  res.data?.avis?.map((a: any) => new Avis(a)) ?? []
        }
      }))
    );
  }
}
