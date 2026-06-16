import { Component, Input, Output } from '@angular/core';
import { EventEmitter } from '@angular/core';
import { Message } from 'src/models/message';
import { ConversationService } from 'src/services/conversations.service';

@Component({
  selector: 'app-message-item',
  templateUrl: './message-item.component.html',
  styleUrls: ['./message-item.component.css']
})
export class MessageItemComponent {

  @Input() message!: Message;
  @Input() isMine = false;
  @Input() convId!: string; 
  @Input() conversationStatus!: 'active' | 'terminee';
  @Output() deleted = new EventEmitter<string>();
  @Output() action = new EventEmitter<'edit' | 'save' | 'delete' | 'cancel'>();

  isEditing = false;
  editedContent = '';

  constructor(private convService: ConversationService) {}

  edit(): void {
    if (this.conversationStatus === 'terminee') return;
    this.isEditing = true;
    this.editedContent = this.message.contenu;
    this.action.emit('edit');
  }

  saveEdit(): void {
    if (!this.editedContent.trim()) return;

    this.convService.updateMessage(
      this.convId,
      this.message.id,
      this.editedContent
    ).subscribe({
      next: () => {
        this.message.contenu = this.editedContent;
        this.isEditing = false;
        this.action.emit('save');
      },
      error: (err) => console.error(err)
    });
  }

  cancelEdit(): void {
    this.isEditing = false;
    this.editedContent = this.message.contenu;
    this.action.emit('cancel');
  }

  delete(): void {
    if (!confirm('Supprimer ce message ?')) return;

    this.convService.deleteMessage(
      this.convId,
      this.message.id
    ).subscribe({
      next: () => {
        this.deleted.emit(this.message.id);
      },
      error: (err) => console.error(err)
    });
  }
}
