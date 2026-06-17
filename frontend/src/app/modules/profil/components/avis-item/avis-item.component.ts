import { Component, Input} from '@angular/core';
import { Avis } from 'src/models/avis';

@Component({
  selector: 'app-avis-item',
  templateUrl: './avis-item.component.html',
  styleUrls: ['./avis-item.component.css']
})
export class AvisItemComponent  {
  @Input() avis!: Avis;

  constructor() { }


}
