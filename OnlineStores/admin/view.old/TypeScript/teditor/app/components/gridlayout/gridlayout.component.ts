import { Component, Input, OnChanges, ChangeDetectionStrategy } from '@angular/core';

//import { Observable } from 'rxjs/rx'
import { Region } from '../../model'
import { Router } from '@angular/router';
//import * as _ from "lodash";

@Component ({
selector: "gridlayout",
//changeDetection: ChangeDetectionStrategy.OnPush,
template: `
        <div class="container-fluid">
            <div *ngIf="headerRegion" class="row" style="width:100%">
                <div class="col-md-12" style="border:1px solid;min-height:30px;background-color:blue" (click)="gotoSpecialRegion(headerRegion.CodeName)">{{headerRegion.CodeName}}</div>
            </div>
            <div *ngFor="let row of gropedRowRegions" class="row" style="width:100%">
                <div *ngFor="let cell of row" class="col-md-{{cell.ColWidth}}" style="border:1px solid;min-height:150px;background-color:gray">
                    {{cell.CodeName}}
                    <!--<glregionsections *ngIf="cell" [objRegion]="cell"></glregionsections>-->
                </div>
            </div>
            <div *ngIf="footerRegion" class="row" style="width:100%">
                <div class="col-md-12" style="border:1px solid;min-height:30px;background-color:blue" (click)="gotoSpecialRegion(footerRegion.CodeName)">{{footerRegion.CodeName}}</div>
            </div>
        </div>
`
})

export class GridlayoutComponent implements OnChanges {
    @Input() gropedRowRegions: Array<any>;
    @Input() headerRegion: Region;
    @Input() footerRegion: Region;


    constructor(private _router: Router) {

    }
    ngOnChanges() {

    }

    gotoSpecialRegion(CodeName: string){
        this._router.navigate(['/specialregion', CodeName]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['specialregion', CodeName]}}]);
    }
    
}