import { Component, OnInit, AfterViewChecked, ElementRef, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ConversationService } from 'src/services/conversations.service';
import { AuthService } from 'src/services/auth.service';
import { Conversation } from 'src/models/conversation';
import { Message } from 'src/models/message';
import { AvisFormData } from 'src/models/avis';

@Component({
  selector: 'app-fil-messages',
  templateUrl: './fil-messages.component.html',
  styleUrls: ['./fil-messages.component.css']
})
export class FilMessagesComponent implements OnInit {

  @ViewChild('messagesEnd') messagesEnd!: ElementRef;

  conversation?: Conversation;
  messages: Message[] = [];
  newMessage = '';
  loading = true;
  sending = false;
  currentUserId = 0;

  showAvisForm = false;
  avisForm: AvisFormData = { note: 5, commentaire: '' };
  noteRange = [1, 2, 3, 4, 5];
  shouldScroll = true;


  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private convService: ConversationService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.currentUserId = this.authService.getUserId() ?? 0;
    const id = this.route.snapshot.paramMap.get('id')!;
    this.convService.getById(id).subscribe({
      next: res => {
        this.conversation = res.data;
        this.messages = res.data.messages ?? [];
        this.loading = false;
        this.convService.markAsRead(id).subscribe();
      },
      error: () => { this.loading = false; }
    });
  }

  ngAfterViewChecked(): void {
    this.scrollToBottom();
  }

  scrollToBottom(): void {
  if (!this.shouldScroll) return;

  try {
    this.messagesEnd?.nativeElement.scrollIntoView({ behavior: 'smooth' });
  } catch {}
}

  sendMessage(): void {
    const content = this.newMessage.trim();
    if (!content || this.sending) return;
    this.sending = true;
    this.convService.sendMessage(this.conversation!.id, content).subscribe({
      next: (res) => {
        this.messages.push(res.data);
        this.newMessage = '';
        this.sending = false;
      },
      error: () => { this.sending = false; }
    });
  }

  terminateConversation(): void {
    if (!confirm('Marquer la conversation comme terminée ?')) return;
    this.convService.terminate(this.conversation!.id).subscribe(() => {
      this.conversation!.status = 'terminee';
    });
  }

  submitAvis(): void {
    this.convService.addAvis(this.conversation!.id, this.avisForm).subscribe(() => {
      this.showAvisForm = false;
    });
  }

  isVendeur(): boolean {
    return this.currentUserId === this.conversation?.vendeur_id;
  }

  hasMyAvis(): boolean {
    return this.conversation?.hasAvisFromUser(this.currentUserId) ?? false;
  }

  onMessageDeleted(messageId: string): void {
    this.messages = this.messages.filter(m => m.id !== messageId);
  }

  handleKeyDown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      this.sendMessage();
    }
  }

  onMessageAction(action: 'edit' | 'save' | 'delete' | 'cancel'): void {
    if (action === 'edit' || 'cancel') {
      this.shouldScroll = false;
    }

    if (action === 'save') {
      this.shouldScroll = true; 
      this.scrollToBottom();
    }
  }

}

