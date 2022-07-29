import { Component } from '@angular/core';

@Component({
  //moduleId: module.id,
  selector: 'gridlayout',
  template: `
               <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" version="1.1" viewBox="0 0 100 100" width="200" height="250">
                   <svg:g gl-rect *ngFor="let rect of rects" [rect]="rect" />
                sss
                </svg>`,
            
  //templateUrl: 'app.component.html',
  //styleUrls: ['app.component.css']
})

export class GridlayoutComponent {
  rects = [
      {x:"0%", y:"0%", width:"100%", height:"10%"},
      {x:"0%", y:"11%", width:"100%", height:"15%"},
      {x:"0%", y:"27%", width:"79%", height:"36%"},
      {x:"80%", y:"27%", width:"20%", height:"36%"},
      {x:"0%", y:"64%", width:"100%", height:"15%"},
      {x:"0%", y:"80%", width:"100%", height:"10%"}
  ];
}


