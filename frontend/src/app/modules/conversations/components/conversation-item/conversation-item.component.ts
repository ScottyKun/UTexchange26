import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Conversation } from 'src/models/conversation';

@Component({
  selector: 'app-conversation-item',
  templateUrl: './conversation-item.component.html',
  styleUrls: ['./conversation-item.component.css']
})
export class ConversationItemComponent{

  @Input() conversation!: Conversation;
  @Input() currentUserId!: number;
  @Output() select = new EventEmitter<string>();

  constructor() { }


}
