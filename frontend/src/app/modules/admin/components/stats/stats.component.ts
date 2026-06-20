import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-stats',
  templateUrl: './stats.component.html',
  styleUrls: ['./stats.component.css']
})
export class StatsComponent {
  
  private readonly powerBiUrl =
    'https://app.powerbi.com/links/wymA7Z_XBu?ctid=502ef359-2fd8-4f48-844f-ef3c798baab8&pbi_source=linkShare';
 
  opened = false;
 
  constructor(private http: HttpClient) {}
 
  openDashboard(): void {
    // Ouvrir Power BI dans un nouvel onglet
    window.open(this.powerBiUrl, '_blank', 'noopener,noreferrer');
    this.opened = true;
  }

}
