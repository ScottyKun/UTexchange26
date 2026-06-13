import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { ConversationsRoutingModule } from './conversations-routing.module';
import { ConversationItemComponent } from './components/conversation-item/conversation-item.component';
import { ConversationListComponent } from './components/conversation-list/conversation-list.component';
import { FilMessagesComponent } from './components/fil-messages/fil-messages.component';
import { MessageItemComponent } from './components/message-item/message-item.component';


@NgModule({
  declarations: [
    ConversationItemComponent,
    ConversationListComponent,
    FilMessagesComponent,
    MessageItemComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    ConversationsRoutingModule
  ]
})
export class ConversationsModule { }
