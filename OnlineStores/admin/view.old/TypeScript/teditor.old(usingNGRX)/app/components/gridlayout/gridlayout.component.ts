import { Component, Input, OnInit } from '@angular/core';
import { Observable } from 'rxjs/rx'
import { Region } from '../../model'
import * as _ from "lodash";

@Component ({
selector: "gridlayout",
template: `
        <div class="container-fluid">
            <!--<div class="row" style="width:100%">
                <div class="col-md-12" style="border:1px solid;min-height:30px;background-color:blue">{{headerRegion.CodeName}}</div>
            </div>-->
            <div *ngFor="let row of gropedRowRegions" class="row" style="width:100%">
                <div *ngFor="let cell of row" class="col-md-{{cell.ColWidth}}" style="border:1px solid;min-height:150px;background-color:gray">
                    {{cell.CodeName}}
                    <!--<glregionsections *ngIf="cell" [objRegion]="cell"></glregionsections>-->
                </div>
            </div>
            <!--<div class="row" style="width:100%">
                <div class="col-md-12" style="border:1px solid;min-height:30px;background-color:blue">{{footerRegion.CodeName}}</div>
            </div>-->
        </div>
`
})

export class GridlayoutComponent implements OnInit {
    @Input() gropedRowRegions: any;
    // objRegions: Observable<Array<Region>>;
    // objRegions = [{Id:1, RowOrder:0, ColOrder:0, Width:12, CodeName:"header", Sections: null, refPage: null},
    //         {Id:2, RowOrder:1, ColOrder:0, Width:12, CodeName:"region1", Sections: null, refPage: null},
    //         {Id:3, RowOrder:2, ColOrder:0, Width:8, CodeName:"region2", Sections: null, refPage: null},
    //         {Id:4, RowOrder:2, ColOrder:1, Width:4, CodeName:"region3", Sections: null, refPage: null},
    //         {Id:5, RowOrder:3, ColOrder:0, Width:12, CodeName:"region4", Sections: null, refPage: null},
    //         {Id:6, RowOrder:4, ColOrder:0, Width:12, CodeName:"footer", Sections: null, refPage: null}];
    
    // headerRegion: Region;
    // footerRegion: Region;
    // groupedRegions: any;

    ngOnInit() {
        console.log("sameh");
        console.log(this.gropedRowRegions);
        // this.headerRegion = _.find(this.objRegions, _.matchesProperty('CodeName', 'header'));
        // this.footerRegion = _.find(this.objRegions, _.matchesProperty('CodeName', 'footer'));

        // this.groupedRegions = Array.from(_(_.filter(this.objRegions, function(region) {
        //     return region.CodeName !== 'header' && region.CodeName !=='footer';
        // })).sortBy('RowOrder').sortBy('ColOrder').groupBy("RowOrder"));
    }
    constructor() {


        //this.groupedRegions =Array.from(_(this.objRegions).sortBy('RowOrder').sortBy('ColOrder').groupBy("RowOrder"));
        //console.log(this.groupeddata);
    }

    // getRegionObject(Id: Number):Region {

    //     return _.find(this.objRegions, _.matchesProperty('Id', Id));
    // }
    
}