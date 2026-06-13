import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ConversationListComponent } from './components/conversation-list/conversation-list.component';
import { FilMessagesComponent }      from './components/fil-messages/fil-messages.component';

const routes: Routes = [
  { path: '',  component: ConversationListComponent },
  { path: ':id', component: FilMessagesComponent }  
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ConversationsRoutingModule { }
