import { Component, Input } from '@angular/core';
import { Message } from 'src/models/message';

@Component({
  selector: 'app-message-item',
  templateUrl: './message-item.component.html',
  styleUrls: ['./message-item.component.css']
})
export class MessageItemComponent {

  @Input() message!: Message;
  @Input() isMine = false;

  constructor() { }

}
