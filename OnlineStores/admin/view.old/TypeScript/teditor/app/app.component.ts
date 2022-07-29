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
  //@ViewChild(SectionComponent) _sectionCompChild: SectionComponent;
  iframeWidth = "75%";
  useHideBar = true;
  //Languages: any = null;
  //currentLanguage: any = null;
  showSaveButton:boolean = false;
  //@ViewChild(SectionComponent)
  private routerComponent: any = null;
  ShowProgressSpinner: boolean;

  constructor(public templateService: TemplateService, public commonService: CommonService, private router: Router, private _location: Location) {
    //templateService.PreviewTemplate("").subscribe();
    commonService.GetResources().subscribe();
    commonService.GetLanguages().subscribe();
    //commonService.GetLanguages().subscribe((langs) => this.Languages = langs);
    //commonService.LanguageObject$.subscribe((lang) => this.currentLanguage = lang);
    //commonService.showSaveButton$.subscribe(s => this.showSaveButton.next(s));
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
    //     // this.templateService.PreviewTemplate().subscribe();
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
  changeIframeWidth(width: string) {
    this.iframeWidth = width;
  }
  toogleBar(e) {
    e.preventDefault();
    this.useHideBar = !this.useHideBar;
    this.iframeWidth = this.useHideBar ? "75%" : "99%";
    //$("iframe").width("97%");
  }
  ngOnInit() {
    this.commonService.ShowProgressSpinner$.subscribe((_showSpinner) => {
      this.ShowProgressSpinner = _showSpinner;
    });
    var objSeparator = $('#separator');
    //debugger;
    objSeparator.css('height', objSeparator.parent().parent().parent().parent().parent().outerHeight() + 'px');
    // $(document).ready(function(){
    //   // Variables
    //   var objMain = $('#main');

    //   // Show sidebar
    //   function showSidebar(){
    //       objMain.addClass('use-sidebar');
    //       //$.cookie('sidebar-pref2', 'use-sidebar', { expires: 30 });
    //   }

    //   // Hide sidebar
    //   function hideSidebar(){
    //       objMain.removeClass('use-sidebar');
    //       //$.cookie('sidebar-pref2', null, { expires: 30 });
    //   }

    //   // Sidebar separator
    //   var objSeparator = $('#separator');

    //   objSeparator.click(function(e){
    //       e.preventDefault();
    //       if ( objMain.hasClass('use-sidebar') ){
    //           hideSidebar();
    //           $("iframe").width("97%");
    //       }
    //       else {
    //           showSidebar();
    //           $("iframe").width("64%");
    //       }
    //   }).css('height', objSeparator.parent().parent().parent().parent().outerHeight() + 'px');

    //   // Load preference
    //   // if ( $.cookie('sidebar-pref2') == null ){
    //   //     objMain.removeClass('use-sidebar');
    //   // }
    // });
  }
}
