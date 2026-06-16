import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from 'src/environnement';

@Injectable({
  providedIn: 'root'
})
export class ChatbotService {
  private readonly url = `${environment.flaskUrl}`;

  constructor(private http: HttpClient) { }

  sendMessage(userMessage: string): Observable<any> {
    return this.http.post<any>(this.url, { message: userMessage });
  }
}