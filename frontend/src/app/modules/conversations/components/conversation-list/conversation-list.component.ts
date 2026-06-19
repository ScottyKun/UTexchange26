import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ConversationService } from 'src/services/conversations.service';
import { AuthService } from 'src/services/auth.service';
import { Conversation } from 'src/models/conversation';

@Component({
  selector: 'app-conversation-list',
  templateUrl: './conversation-list.component.html',
  styleUrls: ['./conversation-list.component.css']
})
export class ConversationListComponent implements OnInit {

  conversations: Conversation[] = [];
  loading = true;
  currentUserId = 0;

  constructor(
    private convService: ConversationService,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.currentUserId = this.authService.getUserId() ?? 0;
    this.convService.getAll().subscribe({
      next: res => { this.conversations = res.data ?? []; this.loading = false; },
      error: () => { this.loading = false; }
    });
  }

  openConversation(id: string): void {
    this.router.navigate(['/conversations', id]);
  }

}
