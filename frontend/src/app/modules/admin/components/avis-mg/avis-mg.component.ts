import { Component, OnInit } from '@angular/core';
import { AdminService } from 'src/services/admin.service';
import { Avis } from 'src/models/avis';

@Component({
  selector: 'app-avis-mg',
  templateUrl: './avis-mg.component.html',
  styleUrls: ['./avis-mg.component.css']
})
export class AvisMgComponent implements OnInit {

  avisList: Avis[]   = [];
  selected: Avis | null  = null;
  loading   = true;
 
  constructor(private adminService: AdminService) {}
 
  ngOnInit(): void { this.loadAvis(); }
 
  loadAvis(): void {
    this.loading = true;
    this.adminService.getAllAvis().subscribe({
      next: res  => { this.avisList = res.data ?? []; this.loading = false; },
      error: ()  => { this.loading = false; }
    });
  }
 
  openModal(a: Avis): void  { this.selected = a; }
  closeModal(): void        { this.selected = null; }
 
  onDeactivate(a: Avis): void {
    if (!confirm('Désactiver cet avis ?')) return;
    const convId = String(a.conversation_id);
    if (!convId) return;
    this.adminService.deactivateAvis(convId, a.id).subscribe(() => this.loadAvis());
  }
 
  onActivate(a: Avis): void {
    const convId = String(a.conversation_id);
    if (!convId) return;
    this.adminService.activateAvis(convId, a.id).subscribe(() => this.loadAvis());
  }
 
  getStars(note: number): number[] {
    return Array(5).fill(0).map((_, i) => i + 1);
  }

}
