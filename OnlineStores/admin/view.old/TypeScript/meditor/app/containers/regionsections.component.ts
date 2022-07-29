import { Component, OnInit } from '@angular/core';
import { Region, Section } from '../model'
import { TemplateService } from '../services/template.service'
import { CommonService } from '../services/common.service'
import { Router, ActivatedRoute, Params } from '@angular/router';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import { DragulaService } from 'ng2-dragula/ng2-dragula'
import * as _ from 'lodash'

@Component({
    selector: "regionsectionsview",
    providers: [DragulaService],
    styleUrls: ['./regionsections.component.css'],
    templateUrl: './regionsections.component.html'
})

export class RegionSectionsComponent implements OnInit {
    regionId: number;
    UserSections: Section[];
    Region$ = new BehaviorSubject<Region>(null);
    maxSortOrder: number = 0;
    availableSectionExist: boolean = false;
    //RegionSections$: BehaviorSubject<Section[]> = new BehaviorSubject<Section[]>([]);
    private routeSub: any;

    constructor(private route: ActivatedRoute, private router: Router, private templateService: TemplateService, private _router: Router, public commonService: CommonService, private dragulaService: DragulaService) {
        // const bag: any = this.dragulaService.find('sections-bag');
        // if (bag !== undefined) {
        //     this.dragulaService.destroy('sections-bag');
        //     this.dragulaService.setOptions('sections-bag', { revertOnSpill: true });
        // }
    }

    ngOnInit() {
        this.commonService.PreviousRoute$.next("/");
        //this.dragulaService.destroy('sections-bag');
        this.dragulaService.setOptions('sections-bag', {
            moves: function (el, container, handle) {
                return handle.className.indexOf('handle-drag') >= 0;
            }
        });
        this.dragulaService.dragend.subscribe(() => {
            console.log(this.UserSections);
            this.reorderSections();
        });

        this.routeSub = this.route.params.subscribe(params => {
            window.scrollTo(0, 0);
            // const bag: any = this.dragulaService.find('sections-bag');
            // if (bag !== undefined) this.dragulaService.destroy('sections-bag');
            // this.dragulaService.setOptions('sections-bag', { revertOnSpill: true });
            this.regionId = +params['id'];

            //this.templateService.isLoaded$.subscribe((loaded) => {
            if (!this.templateService.isLoaded$.getValue())
                this.templateService.LoadTemplate().subscribe();
            //});

            this.templateService.Regions$.subscribe((regions) => {
                //debugger;
                this.Region$.next(_.find<Region>(regions, (_reg) => _reg.id == this.regionId));
            });

            this.Region$.subscribe((reg) => {
                this.commonService.HeaderName$.next(reg ? reg.Name : null);
                this.UserSections = reg ? reg.UserSections : null;
                this.maxSortOrder = reg ? Math.max.apply(Math, reg.UserSections.map(c => parseInt(c.SortOrder.toString()))) : 0;
                this.availableSectionExist = this.commonService.selectedPageCodeName=='home';
            });

            // this.templateService.getRegionAvailableSections(this.regionId).subscribe(
            //     (sections) =>
            //         this.UserSections$.next(sections)
            // );
        });
    }
    ngOnDestroy() {
        this.routeSub.unsubscribe();
    }
    gotoSection(sectionId: any) {
        this._router.navigate(['/section', sectionId]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['section', sectionId]}}]);
    }

    updateSectionName(sectionId: number, newSectionName: string) {
        this.commonService.UpdateObjectName("Section", sectionId, newSectionName).subscribe();
    }

    addSection() {
        this._router.navigate(['/region', this.regionId]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['region', this.regionId]}}]);
    }

    reorderSections() {
        let sectionsOrder: Array<{ SectionId: number, SortOrder: number }> = [];
        //debugger;
        this.UserSections.forEach((section, i) => {
            //debugger;
            sectionsOrder.push({ SectionId: <number>section.id, SortOrder: i + 1 })
        })
        if (sectionsOrder.length > 0)
            this.templateService.ReorderSections(sectionsOrder).subscribe((res) => {
                //console.log(res.status)
            });

        console.log(sectionsOrder);
    }

        removeSection(SectionId: number) {
        this.templateService.RemoveSection(SectionId).subscribe((res) => {
            if(res == "OK") {
                _.remove<Section>(this.UserSections, (s) => s.id == SectionId)
                this.reorderSections();
            }
        });
    }
}