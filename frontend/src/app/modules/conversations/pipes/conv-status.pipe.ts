import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'convStatus'
})
export class ConvStatusPipe implements PipeTransform {

  transform(value: string): string {
    if (!value) return '';

    switch (value.toLowerCase()) {
      case 'active':
        return 'Actif';
      case 'terminee':
        return 'Terminée';
      default:
        return value;
    }
  }
}