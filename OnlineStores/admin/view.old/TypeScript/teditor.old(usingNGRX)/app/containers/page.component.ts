import { Component, Input } from '@angular/core';
import { Page } from '../model'
import { Observable } from 'rxjs/Observable';
import { Store } from '@ngrx/store';
import * as fromRoot from '../datastore/Template.ds';
import * as templateact from '../actions/Template.act';
import * as _ from "lodash";

@Component ({
    selector: "pagecontainer",
    template: `
        <gridlayout *ngIf="objPage" [gropedRowRegions]="gropedRowRegions | async"></gridlayout>
    `
})

export class PageComponent {
    //gropedRowRegions$: Observable<any>;
    @Input() objPage: Page;
    gropedRowRegions: Observable<any>;
    //gropedRowRegions: any;

    constructor(store: Store<fromRoot.State>) {
        //this.gropedRowRegions = store.select(fromRoot.getGropedRowRegions);
        //this.gropedRowRegions = Array.from(_(this.objPage.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RowOrder'));
        //console.log("Page Component");
        //console.log(this.objPage);
        this.gropedRowRegions = store.select( (state) => this.objPage ? Array.from(_(this.objPage.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RowOrder')) : []);
    }

    // get gropedRowRegions() {
    //     if(this.objPage.Regions){
    //         //console.log(Array.from(_(this.objPage.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RowOrder')));
    //         return Array.from(_(this.objPage.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RowOrder')); 
    //     }
    //     else
    //      return [];
    // }
    
}