import { Component, ChangeDetectionStrategy, OnInit } from '@angular/core';
import { Template, Page, Region, Section } from '../model';
import { TemplateService } from '../services/template.service';
import { CommonService } from '../services/common.service';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import 'rxjs/Rx';
import * as _ from 'lodash'

@Component({
    selector: "templatecontainer",
    styleUrls: ['./template.component.css'],
    templateUrl: './template.component.html'
    //changeDetection: ChangeDetectionStrategy.OnPush
})

export class TemplateComponent {
    selectedPage: Page;
    headerRegion: Region;
    footerRegion: Region;
    get selectedPageId(): number {
        if (typeof this.selectedPage != "undefined")
            return this.selectedPage.id;
        else
            return 0;
    }
    set selectedPageId(val) {
        this.commonService.ShowProgressSpinner();
        let allPages = this.templateService.Pages$.getValue();
        this.selectedPage = _.find<Page>(allPages, (c) => { if (val > 0) return c.id == val; else return c.CodeName == "home"; });
        this.commonService.selectedPageCodeName = this.selectedPage.CodeName;
        this.commonService.selectedPageRoute = this.selectedPage.Route;
        this.commonService.HideProgressSpinner();
        this.commonService.PreviewTemplate();
    }
    constructor(public templateService: TemplateService, public commonService: CommonService) {
        this.commonService.PreviousRoute$.next('common/home');
        if(commonService.selectedPageCodeName == "") {
            this.LoadPage("home", "");
        }
        else {
            this.LoadPage(commonService.selectedPageCodeName, commonService.selectedPageRoute);
        }

        //templateService.objTemplate$.subscribe((_template) => {if(_template) this.selectedPage = _template.Pages[0]})
    }

    LoadPage(PageCodeName, PageRoute) {
        this.templateService.LoadTemplate().subscribe(() => {
            this.templateService.objTemplate$.subscribe((_template) => {
                this.selectedPage = _.find<Page>(_template.Pages, (c) => c.CodeName == PageCodeName && c.Route == PageRoute)
                this.commonService.selectedPageCodeName = PageCodeName;
                this.commonService.selectedPageRoute = PageRoute;
                this.commonService.HeaderName$.next(_template.Name);
            });
            this.templateService.HeaderRegion$.subscribe((reg) => this.headerRegion = reg);
            this.templateService.FooterRegion$.subscribe((reg) => this.footerRegion = reg);

        });
    }

    addLayout(Name: string, Route: string) {
        this.templateService.AddLayout(Route, Name).subscribe((e)=> {
            if(e == "OK") {
                this.templateService.LoadTemplate().subscribe(() => {
                    this.templateService.objTemplate$.subscribe((_template) => {
                        this.selectedPage = _.find<Page>(_template.Pages, (c) => c.Route == Route && c.CodeName == "general")
                        this.commonService.selectedPageCodeName = "general";
                        this.commonService.selectedPageRoute = Route;
                        this.commonService.HeaderName$.next(_template.Name);
                    });
                    this.templateService.HeaderRegion$.subscribe((reg) => this.headerRegion = reg);
                    this.templateService.FooterRegion$.subscribe((reg) => this.footerRegion = reg);
        
                });
            }
        });
    }
}