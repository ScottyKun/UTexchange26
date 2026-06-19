import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from 'src/environnement';
import { HttpClient } from '@angular/common/http';
import { ApiResponse } from 'src/models/apiResponse';
import { AvisFormData } from 'src/models/avis';
import { Message } from 'src/models/message';
import { Conversation } from 'src/models/conversation';


@Injectable({ providedIn: 'root' })
export class ConversationService {
 
  private readonly url = `${environment.apiUrl}/conversations`;
 
  constructor(private http: HttpClient) {}
 
  //Conversations 
 
  getAll(): Observable<ApiResponse<Conversation[]>> {
    return this.http.get<ApiResponse<any[]>>(this.url).pipe(
      map(res => ({ ...res, data: res.data?.map((c: any) => new Conversation(c)) ?? [] }))
    );
  }
 
  getById(id: string): Observable<ApiResponse<Conversation>> {
    return this.http.get<ApiResponse<any>>(`${this.url}/${id}`).pipe(
      map(res => ({ ...res, data: new Conversation(res.data) }))
    );
  }
 
  create(annonceId: number): Observable<ApiResponse<{ id: string }>> {
    return this.http.post<ApiResponse<{ id: string }>>(`${this.url}/start`, { annonce_id: annonceId });
  }
 
  terminate(id: string): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${id}/terminate`, {});
  }
 
  delete(id: string): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.url}/${id}`);
  }
 
  //Messages
 
  getMessages(convId: string): Observable<ApiResponse<Message[]>> {
    return this.http.get<ApiResponse<any[]>>(`${this.url}/${convId}/messages`).pipe(
      map(res => ({ ...res, data: res.data?.map((m: any) => new Message(m)) ?? [] }))
    );
  }
 
  sendMessage(convId: string, contenu: string): Observable<ApiResponse<Message>> {
    return this.http.post<ApiResponse<Message>>(`${this.url}/${convId}/messages/send`, { contenu });
  }
 
  deleteMessage(convId: string, messageId: string): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(
      `${environment.apiUrl}/messages/${messageId}/delete`,
      { body: { conv_id: convId } }
    );
  }
 
  updateMessage(convId: string, messageId: string, contenu: string): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(
      `${environment.apiUrl}/messages/${messageId}/update`,
      { conv_id: convId, contenu }
    );
  }
 
  markAsRead(convId: string): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.url}/${convId}/read`, {});
  }
 
  getUnreadCount(): Observable<ApiResponse<{ count: number }>> {
    return this.http.get<ApiResponse<{ count: number }>>(`${environment.apiUrl}/messages/unread-count`);
  }
 
  //Avis 
 
  addAvis(convId: string, data: AvisFormData): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.url}/${convId}/avis`, data);
  }
 
  updateAvis(convId: string, avisId: string, data: AvisFormData): Observable<ApiResponse<null>> {
    return this.http.put<ApiResponse<null>>(`${this.url}/${convId}/avis/${avisId}`, data);
  }
 
  deleteAvis(convId: string, avisId: string): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.url}/${convId}/avis/${avisId}`);
  }
}
