import { Component, Input, OnChanges, OnInit } from '@angular/core';
import { Page, Region, Section } from '../model'
import { Observable } from 'rxjs/Observable';
import { Router } from '@angular/router';
//import { CommonService } from '../services/common.service';
import * as _ from "lodash";

@Component({
    selector: "pagecontainer",
    styleUrls: ['./page.component.css'],
    templateUrl: "./page.component.html"
    //changeDetection: ChangeDetectionStrategy.OnPush,
    //template: `
    //    <gridlayout *ngIf="gropedRowRegions.length>0" [gropedRowRegions]="gropedRowRegions" [headerRegion]="headerRegion" [footerRegion]="footerRegion"></gridlayout>
    //`
})

export class PageComponent implements OnInit, OnChanges {
    @Input() objPage: Page;
    @Input() headerRegion: Region;
    @Input() footerRegion: Region;
    gropedRowRegions: any = [];
    constructor(private _router: Router) {

    }
    ngOnInit() {

    }
    ngOnChanges() {
        if (this.objPage) {
            this.gropedRowRegions = [];
            this.gropedRowRegions = _.values(_(this.objPage.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RowOrder').value());
        }
    }
    gotoRegionSections(regionId: number) {
        this._router.navigate(['/regionsections', regionId]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['regionsections', regionId]}}]);
    }
    gotoSpecialRegion(CodeName: string) {
        this._router.navigate(['/specialregion', CodeName]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['specialregion', CodeName]}}]);
    }

}