import { Component, Input, OnInit } from '@angular/core';
import { Router } from '@angular/router'
import { AppRoutingModule } from '../../app.router'
import { Region, Section, UserSection } from '../../model'
import * as _ from "lodash";

@Component ({
    selector: "glregionsections",
    template: `
        <div class="container-fluid row">
            <div class="row">
                <div *ngFor="let section of objUserRegionSections" class="col-sm-10" style="border:1px solid;height:20px;background-color:white">{{section.Name}} {{section.Region.Id}}</div>
                <div (click)="goToRegion(objRegion.Id)" class="col-sm-10" style="border:1px solid;height:20px;background-color:yellow">+</div>
            </div>
        </div>
    `
})

export class GlRegionSectionsComponent implements OnInit {
    @Input() objRegion: Region;
    objUserRegionSections: Array<UserSection>;
    
    ngOnInit() {
    // this.objUserRegionSections = [
    //         {Id: 1, Name: "UserSection1", Region: this.objRegion, Section: null, SortOrder: 0, UserCollections: null, FieldValues: null, refUserDataVersion: null},
    //         {Id: 2, Name: "UserSection2", Region: this.objRegion, Section: null, SortOrder: 1, UserCollections: null, FieldValues: null, refUserDataVersion: null},
    //         {Id: 3, Name: "UserSection3", Region: this.objRegion, Section: null, SortOrder: 2, UserCollections: null, FieldValues: null, refUserDataVersion: null},
    //         {Id: 4, Name: "UserSection4", Region: this.objRegion, Section: null, SortOrder: 3, UserCollections: null, FieldValues: null, refUserDataVersion: null},
    //         {Id: 5, Name: "UserSection5", Region: this.objRegion, Section: null, SortOrder: 4, UserCollections: null, FieldValues: null, refUserDataVersion: null},
    //     ];
        //console.log(this.objRegion);
    }

    constructor (private _router: Router) {
        
    }

    goToRegion(id:number) {
        this._router.navigate(['/region', this.objRegion.Id]);
    }
}