import { Component, Input, OnInit } from '@angular/core';
import { Router } from '@angular/router'
import { AppRoutingModule } from '../../app.router'
import { Region, Section } from '../../model'
import { DragulaService } from 'ng2-dragula/ng2-dragula'
import { TemplateService } from '../../services/template.service'
import { CommonService } from '../../services/common.service'
import * as _ from "lodash";

@Component ({
    selector: "glregionsections",
    providers: [DragulaService],
    template: `
        <div class="container-fluid row">
            <div class="col-lg-12">
                <div [dragula]='objRegion.CodeName+"-bag"' [dragulaModel]='Sections'>
                    <div *ngFor="let section of Sections" style="overflow: auto;border:1px solid;background-color:white">
                        <div (click)="goToSection(section.id)" class="col-md-10" style="overflow: auto;">
                            <ec-editablelabel [label]="section.Name" (update)="updateSectionName(section.id, $event)"></ec-editablelabel>
                        </div>
                        <div class="col-md-1"> </div>
                        <div class="col-md-1" style="cursor: pointer;cursor: hand;" (click)="removeSection(section.id)">X</div>
                    </div>
                </div>
                <div (click)="goToRegion()" class="col-md-12" style="border:1px solid;height:20px;background-color:yellow">+</div>
            </div>
        </div>
    `
})

export class GlRegionSectionsComponent implements OnInit {
    @Input() objRegion: any;
    Sections: Section[];
    
    ngOnInit() {
        this.Sections = this.objRegion.UserSections;
        this.dragulaService.drop.subscribe(() => {
            //console.log(`drop: ${value[0]}`);
            this.reorderSections();
        });
        // this.templateService.UserSections$.subscribe((_userSection) => {
        //     this.Sections = _.filter<Section>(_userSection, (c)=>c.RegionId == this.objRegion.id);
        //     this.dragulaService.drop.subscribe(() => {
        //         //console.log(`drop: ${value[0]}`);
        //         this.reorderSections();
        //     });
        //     //debugger;
        // });//this.objRegion.UserSections;
    
    }

    constructor (private _router: Router, private dragulaService: DragulaService, private templateService: TemplateService, private commonService: CommonService) {

    }

    reorderSections() {
        let sectionsOrder: Array<{SectionId: number, SortOrder: number}> = [];
        this.Sections.forEach((section, i) => sectionsOrder.push({SectionId: <number>section.id, SortOrder: i+1}))
        if(sectionsOrder.length > 0)
            this.templateService.ReorderSections(sectionsOrder).subscribe((res) => {
                console.log(res.status)
            });
    }

    removeSection(SectionId: number) {
        this.templateService.RemoveSection(SectionId).subscribe((res) => {
            if(res == "OK") {
                //debugger;
                //console.log(this.Sections);
                _.remove<Section>(this.Sections, (s) => s.id == SectionId)
                //this.Sections.splice(index, 1); //remove section from local array
                //console.log(index);
                //console.log(this.Sections);
                this.reorderSections();
            }
        });
    }

    goToRegion() {
        this._router.navigate(['/region', this.objRegion.id]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['region', this.objRegion.id]}}]);
    }

    goToSection(sectionId: number) {
        this._router.navigate(['/section', sectionId]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['section', sectionId]}}]);
    }

    updateSectionName(sectionId: number, newSectionName: string) {
        this.commonService.UpdateObjectName("Section", sectionId, newSectionName).subscribe();
    }
}