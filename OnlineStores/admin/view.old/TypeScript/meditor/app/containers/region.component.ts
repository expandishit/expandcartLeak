import { Component, OnInit, OnDestroy } from '@angular/core';
import { Region, Section } from '../model'
import { TemplateService } from '../services/template.service'
import { CommonService } from '../services/common.service'
import { Router, ActivatedRoute, Params } from '@angular/router';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import * as _ from 'lodash'

@Component({
    selector: "regionview",
    styleUrls: ['./region.component.css'],
    templateUrl: './region.component.html'
})

export class RegionComponent implements OnInit, OnDestroy {
    regionId: number;
    AvailableSections$ = new BehaviorSubject<Section[]>(null);
    Region$ = new BehaviorSubject<Region>(null);
    maxSortOrder: number = 0;
    private routeSub: any;
    //RegionSections$: BehaviorSubject<Section[]> = new BehaviorSubject<Section[]>([]);
    
    constructor(private route: ActivatedRoute, private router: Router, private templateService: TemplateService, private _router: Router, public commonService: CommonService) {
        //this.id = +this.route.snapshot.params['id'];
        this.Region$.subscribe((reg) => {
            this.commonService.HeaderName$.next(reg ? reg.Name : null);
            //debugger;
            this.maxSortOrder = reg ? Math.max.apply(Math, reg.UserSections.map(c => c.SortOrder)) : 0;
        });
    }

    ngOnInit() {
        this.routeSub = this.route.params.subscribe(params => {
            window.scrollTo(0, 0);
            this.regionId = +params['id'];
            this.commonService.PreviousRoute$.next("/regionsections/" + this.regionId);
            this.templateService.Regions$.subscribe((regions) => {
                //debugger;
                this.Region$.next(_.find<Region>(regions, (_reg) => _reg.id == this.regionId));
            });
            this.templateService.getRegionAvailableSections(this.regionId).subscribe(
                (sections) =>
                    this.AvailableSections$.next(sections)
            );
        });
    }
    ngOnDestroy() {
        this.routeSub.unsubscribe();
    }
    insertSection(sectionId: any) {
        this.templateService.insertSection(sectionId, "available", "draft", this.maxSortOrder + 1).subscribe((res) => {
            //this.templateService.mapObjects(res);
            this._router.navigate(['/section', res.insertedSectionId]);
            //this._router.navigate(['/template', {outlets: {'layout-outlet': ['section', res.insertedSectionId]}}]);
        });

    }
}