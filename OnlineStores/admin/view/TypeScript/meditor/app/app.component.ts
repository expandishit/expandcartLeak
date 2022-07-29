import { Component, AfterViewChecked, ViewChild, OnInit } from '@angular/core';
import { Location } from '@angular/common'
import { TemplateService } from './services/template.service';
import { CommonService } from './services/common.service';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { SectionComponent } from './containers/section.component';
import { environment } from 'environments/environment';
//import { ContentChild } from '@angular/core';
//import { BehaviorSubject } from 'rxjs/BehaviorSubject';
declare var $: any;

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  //providers: [SectionComponent]
  //styleUrls: ['./app.component']
})

export class AppComponent implements OnInit {
  useHideBar = true;
  showSaveButton:boolean = false;
  private routerComponent: any = null;
  ShowProgressSpinner: boolean = false;

  constructor(public templateService: TemplateService, public commonService: CommonService, private router: Router, private _location: Location) {

    commonService.GetResources().subscribe();
    commonService.GetLanguages().subscribe();
  }

  publish() {

    this.commonService.Publish().subscribe((res) => alert(res.status == "OK" ? "Published!" : "Not Published!"));
    //alert("Published!");
  }
  // Save() {
  //   this._sectionCompChild.onSubmit();
  // }
  ResetDraftVersion() {
    this.commonService.ResetDraftVersion().subscribe((res) => {
      if (res.status == "OK") {
        window.location.href = window.location.href.slice(0, window.location.href.indexOf("#/"));
        //this.templateService.LoadTemplate().subscribe();
      }
    });
    // .do((res) => {
    //   if(res.status == "OK"){
    //     this.router.navigate(['/']);
    //     // this.templateService
    //   }

    // }).subscribe();
  }
  back() {
    //debugger;
    //this._location.go(this.commonService.PreviousRoute$.getValue());
    if(this.commonService.PreviousRoute$.getValue() == "common/home") {
      window.location.href = window.location.href.slice(0, window.location.href.indexOf("?")) + "?route=common/home&token=" + environment.token;
    } else {
      this.router.navigateByUrl(this.commonService.PreviousRoute$.getValue());
    }
  }
  onActivate(componentRef) {
    this.routerComponent = componentRef;
    //debugger;
    this.showSaveButton = (this.routerComponent instanceof SectionComponent);
    // if(this.routerComponent instanceof SectionComponent) {
    //   this.routerComponent.Languages = this.Languages;
    // }
    //   console.log("is Section Component");
  }
  onSubmit() {
    //debugger;
    this.routerComponent.onSubmit();
  }
  ngOnInit() {
    this.commonService.ShowProgressSpinner$.subscribe((_showSpinner) => {
      this.ShowProgressSpinner = _showSpinner;
    });
  }
}
